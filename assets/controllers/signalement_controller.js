import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        "step",
        "stepLabel",
        "stepBar",
        "typeCard",
        "freqButton",
        "next1",
        "next2",
        "zoneInput",
        "descriptionInput",
        "recapType",
        "recapFrequence",
        "recapZone",
        "recapZoneRow",
        "hiddenType",
        "hiddenZone",
        "hiddenFrequence",
        "hiddenDescription"
    ];

    connect() {
        this.currentStep = 1;
        this.selectedType = "";
        this.selectedFrequence = "";

        this.typeLabels = {
            verbal: "Verbal",
            physique: "Physique",
            cyberharcelement: "Cyberharcèlement",
            exclusion: "Exclusion"
        };

        this.freqLabels = {
            une_fois: "Une fois",
            plusieurs_fois: "Plusieurs fois",
            tous_les_jours: "Tous les jours"
        };
    }

    showStep(n) {
        this.stepTargets.forEach((step) => {
            step.classList.toggle("hidden", parseInt(step.dataset.step) !== n);
        });

        this.stepLabelTarget.textContent = `Étape ${n} sur 4`;

        this.stepBarTargets.forEach((bar) => {
            const step = parseInt(bar.dataset.step);
            bar.style.backgroundColor = step <= n ? "#A855F7" : "#D8B4FE";
        });

        this.currentStep = n;

        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    }

    selectType(event) {
        this.typeCardTargets.forEach((card) => {
            card.classList.remove("border-[#A855F7]", "bg-purple-50");
            card.classList.add("border-transparent");
        });

        const card = event.currentTarget;

        card.classList.remove("border-transparent");
        card.classList.add("border-[#A855F7]", "bg-purple-50");

        this.selectedType = card.dataset.value;
        this.next1Target.disabled = false;
    }

    selectFrequence(event) {
        this.freqButtonTargets.forEach((button) => {
            button.classList.remove("border-[#A855F7]", "text-[#A855F7]");
            button.classList.add("border-gray-200", "text-gray-600");
        });

        const button = event.currentTarget;

        button.classList.remove("border-gray-200", "text-gray-600");
        button.classList.add("border-[#A855F7]", "text-[#A855F7]");

        this.selectedFrequence = button.dataset.value;
        this.next2Target.disabled = false;
    }

    goStep2() {
        this.showStep(2);
    }

    goStep3() {
        this.showStep(3);
    }

    goStep4() {
        const zone = this.zoneInputTarget.value.trim();

        this.recapTypeTarget.textContent =
            this.typeLabels[this.selectedType] || this.selectedType;

        this.recapFrequenceTarget.textContent =
            this.freqLabels[this.selectedFrequence] || this.selectedFrequence;

        if (zone) {
            this.recapZoneTarget.textContent = zone;
            this.recapZoneRowTarget.classList.remove("hidden");
        }

        this.hiddenTypeTarget.value = this.selectedType;
        this.hiddenZoneTarget.value = zone;
        this.hiddenFrequenceTarget.value = this.selectedFrequence;
        this.hiddenDescriptionTarget.value = this.descriptionInputTarget.value;

        this.showStep(4);
    }

    previous() {
        if (this.currentStep > 1) {
            this.showStep(this.currentStep - 1);
        }
    }
}