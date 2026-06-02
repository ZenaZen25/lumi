import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');


// import './styles/app.css';
// import './bootstrap.js';

document.addEventListener('DOMContentLoaded', () => {
    
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('send-btn');
    const typingIndicator = document.getElementById('typing-indicator');
    const realMessagesContainer = document.getElementById('real-messages-container');
    const welcomeScreen = document.getElementById('welcome-screen');
    const quickActionButtons = document.querySelectorAll('.quick-action-btn');

    // Ferma l'esecuzione se non siamo nella pagina della chat
    if (!chatInput) return;

    // Recupera l'URL di Symfony stampato nel data-attribute dell'input
    const chatUrl = chatInput.dataset.url;

    // Funzione per scrollare la chat verso il basso
    function scrollToBottom() {
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    // Funzione di sicurezza per evitare attacchi XSS
    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // Funzione per stampare graficamente un messaggio a schermo
    function appendMessage(text, sender, isAlert = false) {
        // Se c'è la schermata di benvenuto, la eliminiamo al primo messaggio
        if (welcomeScreen) {
            welcomeScreen.remove();
        }

        let messageHtml = '';

        if (sender === 'user') {
            messageHtml = `
                <div class="flex justify-end mb-3">
                    <div class="bg-[#A855F7] text-white rounded-2xl rounded-br-sm px-4 py-3 max-w-xs md:max-w-md text-sm shadow">
                        ${escapeHtml(text)}
                    </div>
                </div>
            `;
        } else {
            const alertBadge = isAlert ? `
                <div class="mt-2 bg-red-50 border border-red-200 rounded-xl px-3 py-2 text-xs text-red-600 font-semibold">
                    ⚠️ Si tu es en danger, appelle le 3018
                </div>` : '';

            messageHtml = `
                <div class="flex justify-start gap-2 items-end mb-3">
                    <img src="/images/chat.png" class="w-7 h-7 object-contain flex-shrink-0" alt="ECHO"/>
                    <div class="bg-white rounded-2xl rounded-bl-sm px-4 py-3 max-w-xs md:max-w-md text-sm shadow text-gray-700">
                        ${escapeHtml(text)}
                        ${alertBadge}
                    </div>
                </div>
            `;
        }

        // Se esiste il contenitore specifico inseriamo lì dentro, altrimenti direttamente nel box principale
        if (realMessagesContainer) {
            realMessagesContainer.insertAdjacentHTML('beforeend', messageHtml);
        } else if (chatMessages) {
            chatMessages.insertAdjacentHTML('beforeend', messageHtml);
        }

        scrollToBottom();
    }

    // Funzione principale per raccogliere l'input ed inviarlo al server
    async function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;

        // Reset dell'input dell'utente
        chatInput.value = '';

        // Mostra il messaggio dell'utente a schermo
        appendMessage(text, 'user');

        // Mostra l'indicatore di scrittura (i tre pallini)
        if (typingIndicator) {
            typingIndicator.classList.remove('hidden');
        }
        scrollToBottom();

        try {
            // Chiamata Fetch Ajax asincrona verso il controller Symfony
            const res = await fetch(chatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: text })
            });

            const data = await res.json();

            // Nascondi i pallini
            if (typingIndicator) {
                typingIndicator.classList.add('hidden');
            }

            // Stampa la risposta del bot
            appendMessage(data.message, 'echo', data.alert);

        } catch (e) {
            // Gestione dell'errore di rete
            if (typingIndicator) {
                typingIndicator.classList.add('hidden');
            }
            appendMessage("Je suis là pour toi 💜 Réessaie dans un instant.", 'echo');
        }
    }

    // --- EVENT LISTENERS (Gestione dei Click e della Tastiera) ---

    // 1. Click sul bottone Invia (icona aeroplanino)
    if (sendBtn) {
        sendBtn.addEventListener('click', sendMessage);
    }

    // 2. Pressione del tasto Invio dentro la casella di testo
    chatInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            sendMessage();
        }
    });

    // 3. Gestione pulsanti Quick Actions della schermata iniziale
    quickActionButtons.forEach(button => {
        button.addEventListener('click', () => {
            const quickText = button.dataset.message;
            if (quickText) {
                chatInput.value = quickText;
                sendMessage();
            }
        });
    });

    // Primo scroll automatico all'apertura della pagina
    scrollToBottom();
});