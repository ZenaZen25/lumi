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





// ======================================================
// WIZARD DE SIGNALEMENT LUMI
// Gestion des 4 étapes du formulaire de signalement
// ======================================================

document.addEventListener('DOMContentLoaded', () => {

    if (!document.getElementById('step-1')) return;

    // Étape actuellement affichée
    let currentStep = 1;

    // Variables qui stockent les choix de l'utilisateur
    let selectedType = '';
    let selectedFrequence = '';

    // Correspondance entre les valeurs techniques
    // et les textes affichés dans le récapitulatif
    const typeLabels = {
        verbal: 'Verbal',
        physique: 'Physique',
        cyberharcelement: 'Cyberharcèlement',
        exclusion: 'Exclusion'
    };

    // Correspondance des fréquences
    const freqLabels = {
        une_fois: 'Une fois',
        plusieurs_fois: 'Plusieurs fois',
        tous_les_jours: 'Tous les jours'
    };

    // ======================================================
    // Fonction qui affiche une étape et masque les autres
    // ======================================================
    function showStep(n) {

        // Parcourt les 4 étapes du wizard
        for (let i = 1; i <= 4; i++) {

            // Affiche uniquement l'étape demandée
            document
                .getElementById('step-' + i)
                .classList.toggle('hidden', i !== n);
        }

        // Mise à jour du texte de progression
        document.getElementById('step-label').textContent =
            'Étape ' + n + ' sur 4';

        // Mise à jour visuelle de la barre de progression
        document.querySelectorAll('.step-bar').forEach(bar => {

            // Numéro de l'étape représentée par la barre
            const s = parseInt(bar.dataset.step);

            // Colore les étapes déjà complétées
            bar.style.backgroundColor =
                s <= n ? '#A855F7' : '#D8B4FE';
        });

        // Sauvegarde de l'étape courante
        currentStep = n;

        // Retour automatique en haut de la page
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // ======================================================
    // ÉTAPE 1 : TYPE DE HARCÈLEMENT
    // ======================================================

    document.querySelectorAll('.type-card').forEach(card => {

        card.addEventListener('click', () => {

            // Réinitialise toutes les cartes
            document.querySelectorAll('.type-card').forEach(c => {

                c.classList.remove(
                    'border-[#A855F7]',
                    'bg-purple-50'
                );

                c.classList.add('border-transparent');
            });

            // Met en évidence la carte sélectionnée
            card.classList.remove('border-transparent');

            card.classList.add(
                'border-[#A855F7]',
                'bg-purple-50'
            );

            // Sauvegarde le type choisi
            selectedType = card.dataset.value;

            // Active le bouton Continuer
            document.getElementById('next-1').disabled = false;
        });
    });

    // Passage à l'étape 2
    document
        .getElementById('next-1')
        .addEventListener('click', () => showStep(2));

    // ======================================================
    // ÉTAPE 2 : FRÉQUENCE DES FAITS
    // ======================================================

    document.querySelectorAll('.freq-btn').forEach(btn => {

        btn.addEventListener('click', () => {

            // Réinitialise tous les boutons fréquence
            document.querySelectorAll('.freq-btn').forEach(b => {

                b.classList.remove(
                    'border-[#A855F7]',
                    'text-[#A855F7]'
                );

                b.classList.add(
                    'border-gray-200',
                    'text-gray-600'
                );
            });

            // Met en évidence le bouton sélectionné
            btn.classList.remove(
                'border-gray-200',
                'text-gray-600'
            );

            btn.classList.add(
                'border-[#A855F7]',
                'text-[#A855F7]'
            );

            // Sauvegarde la fréquence choisie
            selectedFrequence = btn.dataset.value;

            // Active le bouton Continuer
            document.getElementById('next-2').disabled = false;
        });
    });

    // Passage à l'étape 3
    document
        .getElementById('next-2')
        .addEventListener('click', () => showStep(3));

    // ======================================================
    // ÉTAPE 3 : DESCRIPTION ET RÉCAPITULATIF
    // ======================================================

    document
        .getElementById('next-3')
        .addEventListener('click', () => {

            // Récupère le lieu saisi par l'utilisateur
            const zone = document
                .getElementById('input-zone')
                .value
                .trim();

            // Affiche le type dans le récapitulatif
            document.getElementById('recap-type').textContent =
                typeLabels[selectedType] || selectedType;

            // Affiche la fréquence dans le récapitulatif
            document.getElementById('recap-frequence').textContent =
                freqLabels[selectedFrequence] || selectedFrequence;

            // Affiche le lieu seulement s'il a été renseigné
            if (zone) {

                document.getElementById('recap-zone').textContent =
                    zone;

                document
                    .getElementById('recap-zone-row')
                    .classList.remove('hidden');
            }

            // Remplit les champs cachés du formulaire
            // qui seront envoyés au contrôleur Symfony
            document.getElementById('hidden-type').value =
                selectedType;

            document.getElementById('hidden-zone').value =
                zone;

            document.getElementById('hidden-frequence').value =
                selectedFrequence;

            document.getElementById('hidden-description').value =
                document.getElementById('input-description').value;

            // Affiche la dernière étape (récapitulatif)
            showStep(4);
        });

    // ======================================================
    // BOUTONS RETOUR
    // Permet de revenir à l'étape précédente
    // ======================================================

    document.querySelectorAll('.btn-prev').forEach(btn => {

        btn.addEventListener('click', () => {

            // Empêche de revenir avant l'étape 1
            if (currentStep > 1) {

                showStep(currentStep - 1);
            }
        });
    });
});