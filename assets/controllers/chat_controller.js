import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  /**
   * Déclare les éléments HTML utilisés par le contrôleur Stimulus.
   * Ces targets permettent d'interagir avec le chat depuis JavaScript.
   */
  static targets = [
    "chatMessages",
    "chatInput",
    "sendBtn",
    "typingIndicator",
    "realMessagesContainer",
    "welcomeScreen",
  ];

  static values = {
    url: String,
    csrfToken: String,
  };
  /**
   * Méthode appelée automatiquement quand le contrôleur est chargé.
   * Elle place directement la conversation en bas de l'écran.
   */
  connect() {
    this.scrollToBottom();
  }

  /**
   * Fait défiler la zone de messages vers le dernier message.
   */
  scrollToBottom() {
    if (!this.hasChatMessagesTarget) return;

    this.chatMessagesTarget.scrollTop = this.chatMessagesTarget.scrollHeight;
  }

  /**
   * Protège l'affichage contre l'injection de code HTML.
   */
  escapeHtml(text) {
    if (!text) return "";

    return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }

  /**
   * Supprime l'écran d'accueil dès que la conversation commence.
   */
  removeWelcome() {
    if (this.hasWelcomeScreenTarget) {
      this.welcomeScreenTarget.remove();
    }
  }

  /**
   * Ajoute dynamiquement un message dans l'interface.
   */
  appendMessage(text, sender, isAlert = false) {
    this.removeWelcome();

    let html = "";

    if (sender === "user") {
      html = `
        <div class="flex justify-end w-full mb-3">
          <div class="bg-primary-light text-white px-5 py-3 rounded-2xl max-w-[70%] break-words">
            ${this.escapeHtml(text)}
          </div>
        </div>
      `;
    } else {
      const alert = isAlert
        ? `<div class="text-red-600 text-xs mt-2">⚠️ 3018</div>`
        : "";

      html = `
        <div class="flex justify-start w-full mb-3 gap-2">
          <img src="/images/logo.png" class="w-8 h-8 object-contain shrink-0" alt="ECHO"> 
          <div class="bg-white px-5 py-3 rounded-2xl max-w-[70%] break-words">
            ${this.escapeHtml(text)}
            ${alert}
          </div>
        </div>
      `;
    }

    this.realMessagesContainerTarget.insertAdjacentHTML("beforeend", html);
    this.scrollToBottom();
  }

  /**
   * Envoie le message utilisateur au serveur Symfony.
   * Le token CSRF est envoyé avec la requête pour sécuriser l'appel AJAX.
   */
  async sendMessage() {
    const text = this.chatInputTarget.value.trim();

    if (!text) return;

    const url = this.urlValue;
    const csrfToken = this.csrfTokenValue;

    this.chatInputTarget.value = "";
    this.appendMessage(text, "user");
    this.showTyping();

    try {
      const res = await fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          message: text,
          _token: csrfToken,
        }),
      });

      const data = await res.json();

      this.hideTyping();

      if (!res.ok) {
        this.appendMessage(data.error ?? "Erreur serveur", "echo");
        return;
      }

      this.appendMessage(
        data.message ?? "Erreur de réponse",
        "echo",
        data.alert ?? false,
      );
    } catch (e) {
      this.hideTyping();
      console.error("Erreur chat:", e);
      this.appendMessage("Erreur technique : " + e.message, "echo");
    }
  }

  /**
   * Permet d'envoyer le message avec la touche Entrée.
   */
  handleKey(event) {
    if (event.key === "Enter") {
      event.preventDefault();
      this.sendMessage();
    }
  }

  /**
   * Permet d'envoyer rapidement un message prédéfini.
   */
  quickMessage(event) {
    const text = event.currentTarget.dataset.message;

    if (!text) return;

    this.chatInputTarget.value = text;
    this.sendMessage();
  }

  /**
   * Affiche l'indicateur "ECHO écrit..."
   */
  showTyping() {
    if (this.hasTypingIndicatorTarget) {
      this.typingIndicatorTarget.classList.remove("hidden");
    }
  }

  /**
   * Masque l'indicateur de saisie.
   */
  hideTyping() {
    if (this.hasTypingIndicatorTarget) {
      this.typingIndicatorTarget.classList.add("hidden");
    }
  }
}
