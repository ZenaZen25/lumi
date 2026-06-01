<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChatController extends AbstractController
{
    public function __construct(private string $anthropicApiKey) {}

    #[Route('/chat', name: 'app_chat')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Récupère ou crée une conversation en session
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

        return $this->render('chat/index.html.twig', [
            'messages' => $messages,
            'prenom' => $this->getUser()?->getPrenom() ?? 'toi',
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

        // Récupère la conversation
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

        // Construit l'historique pour Claude
        $history = [];
        foreach ($conversation->getMessages() as $msg) {
            $history[] = [
                'role' => $msg->getSender() === 'user' ? 'user' : 'assistant',
                'content' => $msg->getContent(),
            ];
        }
        $history[] = ['role' => 'user', 'content' => $userText];

        // Appel API Claude
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
2. Dire clairement qu'il faut contacter un adulte ou le 3018 (numéro national contre le harcèlement)
3. Retourner dans ta réponse JSON le champ "alert": true

Format de réponse OBLIGATOIRE — réponds UNIQUEMENT en JSON valide, sans aucun texte avant ou après :
{"message": "ta réponse ici", "alert": false}

Si danger détecté :
{"message": "ta réponse ici", "alert": true}
PROMPT;

        try {
            $response = $this->callClaude($systemPrompt, $history);
            $decoded = json_decode($response, true);
            $echoText = $decoded['message'] ?? $response;
            $isAlert = $decoded['alert'] ?? false;
        } catch (\Exception $e) {
            $echoText = "Je suis là pour toi. Peux-tu me dire ce qui se passe ?";
            $isAlert = false;
        }

        // Sauvegarde réponse ECHO
        $echoMsg = new Message();
        $echoMsg->setConversation($conversation);
        $echoMsg->setSender('echo');
        $echoMsg->setContent($echoText);
        $echoMsg->setIsAlert($isAlert);
        $em->persist($echoMsg);

        // Si alerte → marque la conversation
        if ($isAlert) {
            $conversation->setHasAlert(true);
        }

        $conversation->setUpdatedAt(new \DateTime());
        $em->flush();

        return $this->json([
            'message' => $echoText,
            'alert' => $isAlert,
        ]);
    }

    private function callClaude(string $systemPrompt, array $messages): string
    {
        $payload = [
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'system' => $systemPrompt,
            'messages' => $messages,
        ];

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->anthropicApiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result, true);
        return $data['content'][0]['text'] ?? '{"message":"Je suis là pour toi 💜","alert":false}';
    }
}
