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
    /**
     * Affiche la page de chat ECHO.
     *
     * Cette méthode vérifie si une conversation existe déjà
     * dans la session. Si elle n'existe pas, elle crée une
     * nouvelle conversation.
     */
    #[Route('/chat', name: 'app_chat')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Récupère la session Symfony de l'utilisateur
        $session = $request->getSession();

        // Récupère l'identifiant de la conversation stocké en session
        $conversationId = $session->get('conversation_id');

        $conversation = null;

        // Si une conversation existe déjà, on la récupère en base de données
        if ($conversationId) {
            $conversation = $em
                ->getRepository(Conversation::class)
                ->find($conversationId);
        }

        // Si aucune conversation n'existe, on en crée une nouvelle
        if (!$conversation) {
            $conversation = new Conversation();

            // Si l'utilisateur est connecté, on l'associe à la conversation
            // Si l'utilisateur est anonyme, la valeur sera null
            $conversation->setUser($this->getUser());

            $em->persist($conversation);
            $em->flush();

            // On stocke l'id de la conversation dans la session
            // pour pouvoir la retrouver aux prochains messages
            $session->set('conversation_id', $conversation->getId());
        }

        // Récupère tous les messages de la conversation actuelle
        $messages = $conversation->getMessages()->toArray();

        // Vérifie si l'utilisateur est anonyme
        $isAnonymous = !$this->getUser()
            || $session->get('is_anonymous', false);

        // Récupère le prénom si l'utilisateur est connecté,
        // sinon on affiche "toi"
        $prenom = $this->getUser()?->getPrenom() ?? 'toi';

        // Envoie les données à la vue Twig
        return $this->render('chat/index.html.twig', [
            'messages'     => $messages,
            'prenom'       => $prenom,
            'is_anonymous' => $isAnonymous,
        ]);
    }

    /**
     * Reçoit un message envoyé par AJAX depuis Stimulus.
     *
     * Cette méthode :
     * - récupère le message de l'utilisateur ;
     * - sauvegarde le message en base de données ;
     * - envoie le message à OpenRouter ;
     * - récupère la réponse de l'IA ;
     * - sauvegarde la réponse d'ECHO ;
     * - crée une alerte si un danger est détecté ;
     * - retourne une réponse JSON au frontend.
     */
    #[Route('/chat/message', name: 'app_chat_message', methods: ['POST'])]
    public function sendMessage(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Récupère le contenu JSON envoyé par le fetch JavaScript
        $data = json_decode($request->getContent(), true);

        // Récupère le message de l'utilisateur et supprime les espaces inutiles
        $userText = trim($data['message'] ?? '');

        // Si le message est vide, on retourne une erreur
        if (!$userText) {
            return $this->json(['error' => 'Message vide'], 400);
        }

        // Récupère la session Symfony
        $session = $request->getSession();

        // Récupère l'identifiant de conversation enregistré dans la session
        $conversationId = $session->get('conversation_id');

        // Si une conversation existe, on la récupère
        $conversation = $conversationId
            ? $em->getRepository(Conversation::class)->find($conversationId)
            : null;

        // Si aucune conversation n'existe, on en crée une nouvelle
        if (!$conversation) {
            $conversation = new Conversation();

            // Associe la conversation à l'utilisateur connecté si disponible
            $conversation->setUser($this->getUser());

            $em->persist($conversation);
            $em->flush();

            // Stocke la conversation en session
            $session->set('conversation_id', $conversation->getId());
        }

        // ==============================
        // SAUVEGARDE DU MESSAGE UTILISATEUR
        // ==============================

        // Crée une nouvelle entité Message pour l'utilisateur
        $userMsg = new Message();

        // Associe le message à la conversation actuelle
        $userMsg->setConversation($conversation);

        // Indique que le message vient de l'utilisateur
        $userMsg->setSender('user');

        // Enregistre le contenu du message
        $userMsg->setContent($userText);

        // Sauvegarde le message utilisateur en base de données
        $em->persist($userMsg);
        $em->flush();

        // ==============================
        // PROMPT SYSTÈME POUR ECHO
        // ==============================
        //
        // Ce prompt définit le rôle, le ton et les règles de sécurité d'ECHO.
        // Il oblige aussi le modèle IA à répondre en JSON.
        //
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

        // Récupère la clé API OpenRouter depuis les variables d'environnement.
        // La clé ne doit jamais être écrite directement dans le code.
        $apiKey = $_ENV['OPENROUTER_API_KEY']
            ?? $_SERVER['OPENROUTER_API_KEY']
            ?? getenv('OPENROUTER_API_KEY');

        try {
            // Crée un client HTTP Symfony pour appeler l'API externe
            $client = \Symfony\Component\HttpClient\HttpClient::create();

            // Envoie la requête POST vers OpenRouter
            $response = $client->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    // Authentification avec la clé API
                    'Authorization' => 'Bearer ' . $apiKey,

                    // Indique que les données envoyées sont en JSON
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    // Modèle IA utilisé via OpenRouter
                    'model' => 'google/gemini-2.5-flash',

                    // Messages envoyés au modèle :
                    // - system : règle le comportement d'ECHO
                    // - user : contient le message de l'utilisateur
                    
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $userText],
                    ],

                    // Contrôle la créativité de la réponse
                    'temperature' => 0.7,

                    // Limite la taille maximale de la réponse
                    'max_tokens' => 500,
                ],
            ]);

            // Récupère la réponse brute de l'API
            $content = $response->getContent(false);

            // Transforme la réponse JSON en tableau PHP
            $data = json_decode($content, true);

            // Extrait le texte généré par l'IA
            $responseText = $data['choices'][0]['message']['content'] ?? null;

            // Si aucune réponse n'est reçue, on déclenche une erreur
            if (!$responseText) {
                throw new \Exception($data['error']['message'] ?? 'Réponse vide');
            }

            // ==============================
            // NETTOYAGE DE LA RÉPONSE IA
            // ==============================
            //
            // Certains modèles ajoutent parfois ```json autour de la réponse.
            // On les supprime pour pouvoir décoder correctement le JSON.
            //
            $responseText = trim($responseText);
            $responseText = preg_replace('/^```json\s*/i', '', $responseText);
            $responseText = preg_replace('/^```\s*/i', '', $responseText);
            $responseText = preg_replace('/\s*```$/', '', $responseText);

            // Décode la réponse JSON attendue :
            // {"message": "...", "alert": false, "severite": "low"}
            $decoded = json_decode($responseText, true);

            // Si la réponse est bien un JSON valide
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['message'])) {
                $echoText = $decoded['message'];
                $isAlert  = (bool)($decoded['alert'] ?? false);
                $severite = $decoded['severite'] ?? 'low';
            } else {
                // Si l'IA ne respecte pas le format JSON,
                // on affiche quand même sa réponse brute
                $echoText = $responseText;
                $isAlert  = false;
                $severite = 'low';
            }
        } catch (\Throwable $e) {
            // En cas d'erreur réseau ou API,
            // ECHO envoie une réponse de secours
            $echoText = 'Je suis là pour toi 💜 Peux-tu me dire ce qui se passe ?';
            $isAlert  = false;
            $severite = 'low';
        }

        // ==============================
        // SAUVEGARDE DE LA RÉPONSE ECHO
        // ==============================

        // Crée un message pour la réponse d'ECHO
        $echoMsg = new Message();

        // Associe la réponse à la même conversation
        $echoMsg->setConversation($conversation);

        // Indique que le message vient d'ECHO
        $echoMsg->setSender('echo');

        // Contenu de la réponse générée
        $echoMsg->setContent($echoText);

        // Indique si ce message a déclenché une alerte
        $echoMsg->setIsAlert($isAlert);

        $em->persist($echoMsg);

        // ==============================
        // CRÉATION AUTOMATIQUE D'ALERTE
        // ==============================
        //
        // Si l'IA détecte un danger immédiat,
        // on crée un Signalement et une Alerte pour l'administration.
        //
        if ($isAlert && !$conversation->isHasAlert()) {
            // Évite de créer plusieurs alertes pour la même conversation
            $conversation->setHasAlert(true);

            // Récupère l'établissement de l'utilisateur connecté
            $etablissement = $this->getUser()?->getEtablissement();

            // Une alerte automatique est créée uniquement
            // si un établissement est disponible
            if ($etablissement) {
                $signalement = new Signalement();

                $signalement->setType('chat_automatique');
                $signalement->setZone('chat_echo');
                $signalement->setSeverite($severite);

                // On garde le message original comme preuve/context
                $signalement->setDescription(
                    '⚠️ Danger détecté par ECHO. Message : ' . $userText
                );

                $signalement->setStatut('nouveau');
                $signalement->setUser($this->getUser());
                $signalement->setAnonymousToken($session->getId());
                $signalement->setCreatedAt(new \DateTimeImmutable());
                $signalement->setUpdatedAt(new \DateTime());

                // Rattachement à l'établissement
                $signalement->setEtablissement($etablissement);

                $em->persist($signalement);
                $em->flush();

                // Création de l'alerte liée au signalement
                $alerte = new Alerte();

                $alerte->setSeverite($severite);
                $alerte->setStatut('nouveau');
                $alerte->setNoteInterne('Alerte automatique générée par ECHO.');
                $alerte->setCreatedAt(new \DateTimeImmutable());
                $alerte->setSignalement($signalement);

                $em->persist($alerte);
            }
        }

        // Met à jour la date de dernière activité de la conversation
        $conversation->setUpdatedAt(new \DateTime());

        // Sauvegarde finale de tous les changements
        $em->flush();

        // Réponse JSON envoyée au JavaScript Stimulus
        return $this->json([
            'message' => $echoText,
            'alert'   => $isAlert,
        ]);
    }
}