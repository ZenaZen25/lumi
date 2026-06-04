import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = [
    "chatMessages",
    "chatInput",
    "sendBtn",
    "typingIndicator",
    "realMessagesContainer",
    "welcomeScreen",
  ];

  connect() {
    console.log("Chat controller connecté");
    this.scrollToBottom();
  }

  scrollToBottom() {
    if (!this.hasChatMessagesTarget) return;

    this.chatMessagesTarget.scrollTop = this.chatMessagesTarget.scrollHeight;
  }

  escapeHtml(text) {
    if (!text) return "";

    return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }

  removeWelcome() {
    if (this.hasWelcomeScreenTarget) {
      this.welcomeScreenTarget.remove();
    }
  }

  appendMessage(text, sender, isAlert = false) {
    this.removeWelcome();
    console.log("IMAGE UTILISÉE: /images/logo.png");

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

  async sendMessage() {
    console.log("sendMessage appelé");

    const text = this.chatInputTarget.value.trim();
    console.log(text);

    if (!text) return;

    const url = this.chatInputTarget.dataset.url;

    this.chatInputTarget.value = "";
    this.appendMessage(text, "user");
    this.showTyping();

    try {
      const res = await fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ message: text }),
      });

      const data = await res.json();

      this.hideTyping();

      this.appendMessage(
        data.message ?? "Erreur de réponse",
        "echo",
        data.alert ?? false,
      );
    } catch (e) {
      this.hideTyping();
      this.appendMessage("Erreur 💜", "echo");
    }
  }

  handleKey(event) {
    if (event.key === "Enter") {
      event.preventDefault();
      this.sendMessage();
    }
  }

  quickMessage(event) {
    const text = event.currentTarget.dataset.message;

    if (!text) return;

    this.chatInputTarget.value = text;
    this.sendMessage();
  }

  showTyping() {
    if (this.hasTypingIndicatorTarget) {
      this.typingIndicatorTarget.classList.remove("hidden");
    }
  }

  hideTyping() {
    if (this.hasTypingIndicatorTarget) {
      this.typingIndicatorTarget.classList.add("hidden");
    }
  }
}
