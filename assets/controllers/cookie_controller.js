import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["banner"];

  connect() {
    if (!localStorage.getItem("lumi_rgpd")) {
      this.bannerTarget.classList.remove("hidden");
      document.body.classList.add("overflow-hidden");
    }
  }
// connect() {
//     this.bannerTarget.classList.remove("hidden");
// }

  accept() {
    localStorage.setItem("lumi_rgpd", "accepted");
    this.bannerTarget.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
  }

  refuse() {
    localStorage.removeItem("lumi_rgpd");
    window.location.href = "/";
  }
}

