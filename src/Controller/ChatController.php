<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Signalement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChatController extends AbstractController
{
    #[Route('/chat', name: 'app_chat')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $conversationId = $session->get('conversation_id');
        $conversation = null;

        if ($conversationId) {
            $conversation = $em->getRepository(Conversation::class)->find($conversationId);
        }

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setUser($this->getUser()); // null si anonyme
            $em->persist($conversation);
            $em->flush();
            $session->set('conversation_id', $conversation->getId());
        }

        $messages = $conversation->getMessages()->toArray();
        $isAnonymous = !$this->getUser() || $session->get('is_anonymous', false);
        $prenom = $this->getUser()?->getPrenom() ?? 'toi';

        return $this->render('chat/index.html.twig', [
            'messages'     => $messages,
            'prenom'       => $prenom,
            'is_anonymous' => $isAnonymous,
        ]);
    }

    #[Route('/chat/message', name: 'app_chat_message', methods: ['POST'])]
    public function sendMessage(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userText = trim($data['message'] ?? '');

        if (!$userText) {
            return $this->json(['error' => 'Message vide'], 400);
        }

        $session = $request->getSession();
        $conversationId = $session->get('conversation_id');

        $conversation = $conversationId
            ? $em->getRepository(Conversation::class)->find($conversationId)
            : null;

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setUser($this->getUser());
            $em->persist($conversation);
            $em->flush();
            $session->set('conversation_id', $conversation->getId());
        }

        // Sauvegarde message utilisateur
        $userMsg = new Message();
        $userMsg->setConversation($conversation);
        $userMsg->setSender('user');
        $userMsg->setContent($userText);
        $em->persist($userMsg);
        $em->flush();

        $systemPrompt = <<<PROMPT
Tu es ECHO, le compagnon bienveillant et empathique de la plateforme LUMI.
Tu aides les élèves victimes de harcèlement scolaire ou d'intimidation.
Tu parles toujours avec douceur, sans juger, en utilisant un langage simple adapté aux enfants et adolescents (8-16 ans).
Tu écoutes, tu valides les émotions, tu rassures.

Tes valeurs :
- Jamais de jugement
- Toujours bienveillant
- Confidentiel et sécurisé
- Tu proposes toujours de parler à un adulte de confiance (professeur, CPE, parent) ou à la direction quand c'est pertinent.

RÈGLE CRITIQUE DE SÉCURITÉ :
Si le message contient des indices de danger immédiat (violence physique grave, menace de mort, automutilation, idées suicidaires, abus sexuel), tu dois :
1. Répondre avec empathie et calme
2. Dire clairement qu'il faut contacter un adulte ou le 3018
3. Retourner "alert": true et "severite": "high"

Format OBLIGATOIRE — réponds UNIQUEMENT en JSON valide, sans texte avant ou après :
{"message": "ta réponse ici", "alert": false, "severite": "low"}

Valeurs possibles pour severite : "low", "medium", "high"
PROMPT;

        $apiKey = $_ENV['OPENROUTER_API_KEY']
            ?? $_SERVER['OPENROUTER_API_KEY']
            ?? getenv('OPENROUTER_API_KEY');

        try {
            $client = \Symfony\Component\HttpClient\HttpClient::create();

            $response = $client->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'google/gemini-2.5-flash',
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $userText],
                    ],
                    'temperature' => 0.7,
                    'max_tokens'  => 500,
                ],
            ]);

            $content = $response->getContent(false);
            $data = json_decode($content, true);
            $responseText = $data['choices'][0]['message']['content'] ?? null;

            if (!$responseText) {
                throw new \Exception($data['error']['message'] ?? 'Réponse vide');
            }

            // Nettoie les éventuels backticks markdown
            $responseText = trim($responseText);
            $responseText = preg_replace('/^```json\s*/i', '', $responseText);
            $responseText = preg_replace('/^```\s*/i', '', $responseText);
            $responseText = preg_replace('/\s*```$/', '', $responseText);

            $decoded = json_decode($responseText, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['message'])) {
                $echoText = $decoded['message'];
                $isAlert  = (bool)($decoded['alert'] ?? false);
                $severite = $decoded['severite'] ?? 'low';
            } else {
                $echoText = $responseText;
                $isAlert  = false;
                $severite = 'low';
            }
        } catch (\Throwable $e) {
            $echoText = 'Je suis là pour toi 💜 Peux-tu me dire ce qui se passe ?';
            $isAlert  = false;
            $severite = 'low';
        }

        // Sauvegarde réponse ECHO
        $echoMsg = new Message();
        $echoMsg->setConversation($conversation);
        $echoMsg->setSender('echo');
        $echoMsg->setContent($echoText);
        $echoMsg->setIsAlert($isAlert);
        $em->persist($echoMsg);

        // Si alerte détectée → crée Signalement + Alerte pour l'admin
        if ($isAlert && !$conversation->isHasAlert()) {
            $conversation->setHasAlert(true);

            $etablissement = $this->getUser()?->getEtablissement();
            if ($etablissement) {
                $signalement = new Signalement();
                $signalement->setType('chat_automatique');
                $signalement->setZone('chat_echo');
                $signalement->setSeverite($severite);
                $signalement->setDescription('⚠️ Danger détecté par ECHO. Message : ' . $userText);
                $signalement->setStatut('nouveau');
                $signalement->setUser($this->getUser());
                $signalement->setAnonymousToken($session->getId());
                $signalement->setCreatedAt(new \DateTimeImmutable());
                $signalement->setUpdatedAt(new \DateTime());
                $signalement->setEtablissement($etablissement);
                $em->persist($signalement);
                $em->flush();

                $alerte = new Alerte();
                $alerte->setSeverite($severite);
                $alerte->setStatut('nouveau');
                $alerte->setNoteInterne('Alerte automatique générée par ECHO.');
                $alerte->setCreatedAt(new \DateTimeImmutable());
                $alerte->setSignalement($signalement);
                $em->persist($alerte);
            }
        }

        $conversation->setUpdatedAt(new \DateTime());
        $em->flush();

        return $this->json([
            'message' => $echoText,
            'alert'   => $isAlert,
        ]);
    }
}
