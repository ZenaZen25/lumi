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
            $conversation->setUser($this->getUser());
            $em->persist($conversation);
            $em->flush();
            $session->set('conversation_id', $conversation->getId());
        }

        $messages = $conversation->getMessages()->toArray();

        return $this->render('chat/index.html.twig', [
            'messages' => $messages,
            'prenom'   => $this->getUser()?->getPrenom() ?? 'toi',
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

        // USER MESSAGE
        $userMsg = new Message();
        $userMsg->setConversation($conversation);
        $userMsg->setSender('user');
        $userMsg->setContent($userText);

        $em->persist($userMsg);
        $em->flush();

        $systemPrompt = "
Tu es ECHO, un assistant bienveillant.

Réponds simplement en JSON:
{
  \"message\": \"réponse ici\",
  \"alert\": false
}
";

        $apiKey = $_ENV['GEMINI_API_KEY']
            ?? $_SERVER['GEMINI_API_KEY']
            ?? getenv('GEMINI_API_KEY');

        try {
            $client = \Symfony\Component\HttpClient\HttpClient::create();

            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

            $response = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $systemPrompt . "\n\nMessage: " . $userText
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 512,
                    ]
                ]
            ]);

            $content = $response->getContent(false);
            $data = json_decode($content, true);

            $responseText =
                $data['candidates'][0]['content']['parts'][0]['text']
                ?? null;

            if (!$responseText) {
                throw new \Exception("Réponse vide de Gemini");
            }

            $responseText = trim($responseText);
            $decoded = json_decode($responseText, true);

            if (!$decoded || !isset($decoded['message'])) {
                $echoText = $responseText;
                $isAlert = false;
            } else {
                $echoText = $decoded['message'];
                $isAlert = (bool) ($decoded['alert'] ?? false);
            }
        } catch (\Throwable $e) {
            $echoText = "Erreur API: " . $e->getMessage();
            $isAlert = false;
        }

        $echoMsg = new Message();
        $echoMsg->setConversation($conversation);
        $echoMsg->setSender('echo');
        $echoMsg->setContent($echoText);
        $echoMsg->setIsAlert($isAlert);

        $em->persist($echoMsg);

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
}
