/**
 * ============================================================================
 * SAE203 E-LLUSION - JavaScript principal
 * ============================================================================
 * Ce fichier contient tous les scripts JavaScript du site :
 * - Carrousel d'images (paramétrable)
 * - Menu burger (mobile)
 * - Gestion dynamique des créneaux (Fetch API)
 * - Activation/désactivation de la checkbox buffet
 * - Validation des formulaires
 * - Messages flash
 * 
 * JavaScript vanilla uniquement (pas de jQuery ni de framework)
 * ============================================================================
 */

// Attendre que le DOM soit chargé avant d'exécuter les scripts
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // ========================================================================
    // 1. MENU BURGER (Navigation mobile)
    // ========================================================================
    
    /**
     * Gère l'ouverture/fermeture du menu burger sur mobile.
     * Toggle la classe 'nav-open' sur la navigation et met à jour aria-expanded.
     */
    function initBurgerMenu() {
        const burgerButton = document.querySelector('.burger-menu');
        const navigation = document.querySelector('.nav-principale');
        
        if (!burgerButton || !navigation) return;
        
        burgerButton.addEventListener('click', function() {
            // Toggle de l'état ouvert/fermé
            const isOpen = navigation.classList.toggle('nav-open');
            
            // Mise à jour de l'attribut aria-expanded pour l'accessibilité
            burgerButton.setAttribute('aria-expanded', isOpen);
            
            // Mise à jour du label selon l'état
            const label = isOpen ? 'Fermer le menu de navigation' : 'Ouvrir le menu de navigation';
            burgerButton.setAttribute('aria-label', label);
        });
        
        // Fermer le menu quand on clique sur un lien
        const navLinks = navigation.querySelectorAll('.nav-link');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                navigation.classList.remove('nav-open');
                burgerButton.setAttribute('aria-expanded', 'false');
            });
        });
        
        // Fermer le menu quand on clique en dehors
        document.addEventListener('click', function(event) {
            if (!navigation.contains(event.target) && !burgerButton.contains(event.target)) {
                navigation.classList.remove('nav-open');
                burgerButton.setAttribute('aria-expanded', 'false');
            }
        });
    }
    
    // ========================================================================
    // 2. CARROUSEL D'IMAGES
    // ========================================================================
    
    /**
     * Initialise le carrousel d'images.
     * Fonctionnalités :
     * - Navigation par flèches (précédent/suivant)
     * - Navigation par indicateurs (points)
     * - Défilement automatique (avec pause au survol)
     * - Support tactile basique
     */
    function initCarrousel() {
        const carrousel = document.querySelector('.carrousel');
        if (!carrousel) return;
        
        const container = carrousel.querySelector('.carrousel-container');
        const slides = carrousel.querySelectorAll('.carrousel-slide');
        const prevBtn = carrousel.querySelector('.carrousel-btn-prev');
        const nextBtn = carrousel.querySelector('.carrousel-btn-next');
        const indicatorsContainer = carrousel.querySelector('.carrousel-indicators');
        
        if (!container || slides.length === 0) return;
        
        let currentIndex = 0;
        let autoPlayInterval = null;
        const autoPlayDelay = 5000; // 5 secondes entre chaque slide
        
        /**
         * Affiche le slide à l'index donné
         * @param {number} index - Index du slide à afficher
         */
        function goToSlide(index) {
            // Gestion du bouclage
            if (index < 0) {
                index = slides.length - 1;
            } else if (index >= slides.length) {
                index = 0;
            }
            
            currentIndex = index;
            
            // Déplacement du conteneur
            container.style.transform = `translateX(-${currentIndex * 100}%)`;
            
            // Mise à jour des indicateurs
            updateIndicators();
        }
        
        /**
         * Passe au slide suivant
         */
        function nextSlide() {
            goToSlide(currentIndex + 1);
        }
        
        /**
         * Passe au slide précédent
         */
        function prevSlide() {
            goToSlide(currentIndex - 1);
        }
        
        /**
         * Met à jour l'état des indicateurs (points)
         */
        function updateIndicators() {
            if (!indicatorsContainer) return;
            
            const indicators = indicatorsContainer.querySelectorAll('.carrousel-indicator');
            indicators.forEach(function(indicator, index) {
                if (index === currentIndex) {
                    indicator.classList.add('active');
                    indicator.setAttribute('aria-current', 'true');
                } else {
                    indicator.classList.remove('active');
                    indicator.removeAttribute('aria-current');
                }
            });
        }
        
        /**
         * Crée les indicateurs (points) pour chaque slide
         */
        function createIndicators() {
            if (!indicatorsContainer) return;
            
            slides.forEach(function(slide, index) {
                const indicator = document.createElement('button');
                indicator.classList.add('carrousel-indicator');
                indicator.setAttribute('aria-label', `Aller au slide ${index + 1}`);
                
                if (index === 0) {
                    indicator.classList.add('active');
                    indicator.setAttribute('aria-current', 'true');
                }
                
                indicator.addEventListener('click', function() {
                    goToSlide(index);
                    resetAutoPlay();
                });
                
                indicatorsContainer.appendChild(indicator);
            });
        }
        
        /**
         * Démarre le défilement automatique
         */
        function startAutoPlay() {
            if (autoPlayInterval) return;
            autoPlayInterval = setInterval(nextSlide, autoPlayDelay);
        }
        
        /**
         * Arrête le défilement automatique
         */
        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }
        
        /**
         * Réinitialise le timer du défilement automatique
         */
        function resetAutoPlay() {
            stopAutoPlay();
            startAutoPlay();
        }
        
        // Écouteurs d'événements pour les boutons
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                prevSlide();
                resetAutoPlay();
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                nextSlide();
                resetAutoPlay();
            });
        }
        
        // Pause au survol de la souris
        carrousel.addEventListener('mouseenter', stopAutoPlay);
        carrousel.addEventListener('mouseleave', startAutoPlay);
        
        // Navigation au clavier
        carrousel.addEventListener('keydown', function(event) {
            if (event.key === 'ArrowLeft') {
                prevSlide();
                resetAutoPlay();
            } else if (event.key === 'ArrowRight') {
                nextSlide();
                resetAutoPlay();
            }
        });
        
        // Support tactile basique (swipe)
        let touchStartX = 0;
        let touchEndX = 0;
        
        carrousel.addEventListener('touchstart', function(event) {
            touchStartX = event.changedTouches[0].screenX;
        }, { passive: true });
        
        carrousel.addEventListener('touchend', function(event) {
            touchEndX = event.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (diff > swipeThreshold) {
                // Swipe vers la gauche -> slide suivant
                nextSlide();
                resetAutoPlay();
            } else if (diff < -swipeThreshold) {
                // Swipe vers la droite -> slide précédent
                prevSlide();
                resetAutoPlay();
            }
        }
        
        // Initialisation
        createIndicators();
        startAutoPlay();
    }
    
    // ========================================================================
    // 3. GESTION DES CRÉNEAUX (Fetch API)
    // ========================================================================
    
    /**
     * Charge les créneaux disponibles pour une salle donnée via l'API.
     * Met à jour dynamiquement le select des créneaux.
     */
    function initCreneauxFetch() {
        const salleSelect = document.getElementById('id_salle');
        const creneauSelect = document.getElementById('id_creneau');
        const nbPersonnesInput = document.getElementById('nb_personnes');
        
        if (!salleSelect || !creneauSelect) return;
        
        /**
         * Charge les créneaux pour la salle sélectionnée
         */
        async function chargerCreneaux() {
            const idSalle = salleSelect.value;
            
            // Réinitialiser le select des créneaux
            creneauSelect.innerHTML = '<option value="">-- Chargement... --</option>';
            creneauSelect.disabled = true;
            
            if (!idSalle) {
                creneauSelect.innerHTML = '<option value="">-- Sélectionnez d\'abord une salle --</option>';
                return;
            }
            
            try {
                // Appel à l'API (utilise SITE_URL global défini dans header.php)
                const apiUrl = window.SITE_URL ? `${window.SITE_URL}/api/creneaux.php` : 'api/creneaux.php';
                const response = await fetch(`${apiUrl}?id_salle=${encodeURIComponent(idSalle)}`);
                
                if (!response.ok) {
                    throw new Error('Erreur lors du chargement des créneaux');
                }
                
                const creneaux = await response.json();
                
                // Vider et reconstruire le select
                creneauSelect.innerHTML = '<option value="">-- Choisir un créneau --</option>';
                
                if (creneaux.length === 0) {
                    creneauSelect.innerHTML = '<option value="">Aucun créneau disponible</option>';
                    return;
                }
                
                // Grouper par date
                let currentDate = '';
                
                creneaux.forEach(function(creneau) {
                    // Créer un groupe optgroup pour chaque nouvelle date
                    if (creneau.date_formatee !== currentDate) {
                        currentDate = creneau.date_formatee;
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = currentDate;
                        creneauSelect.appendChild(optgroup);
                    }
                    
                    // Créer l'option
                    const option = document.createElement('option');
                    option.value = creneau.id_creneau;
                    
                    const placesRestantes = parseInt(creneau.places_restantes, 10);
                    
                    // Texte de l'option avec indication des places
                    let texte = creneau.heure_formatee;
                    
                    if (placesRestantes <= 0) {
                        texte += ' - COMPLET';
                        option.disabled = true;
                        option.style.color = '#af0000';
                    } else if (placesRestantes <= 3) {
                        texte += ` - ${placesRestantes} place(s) restante(s)`;
                        option.style.color = '#ed6c02';
                    } else {
                        texte += ` - ${placesRestantes} places`;
                    }
                    
                    option.textContent = texte;
                    option.dataset.placesRestantes = placesRestantes;
                    
                    // Ajouter au dernier optgroup
                    const lastOptgroup = creneauSelect.querySelector('optgroup:last-of-type');
                    if (lastOptgroup) {
                        lastOptgroup.appendChild(option);
                    } else {
                        creneauSelect.appendChild(option);
                    }
                });
                
                creneauSelect.disabled = false;
                
            } catch (error) {
                console.error('Erreur:', error);
                creneauSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            }
        }
        
        /**
         * Vérifie si le nombre de personnes est valide pour le créneau sélectionné
         */
        function verifierPlaces() {
            if (!creneauSelect.value || !nbPersonnesInput) return;
            
            const selectedOption = creneauSelect.options[creneauSelect.selectedIndex];
            const placesRestantes = parseInt(selectedOption.dataset.placesRestantes || 12, 10);
            const nbPersonnes = parseInt(nbPersonnesInput.value || 1, 10);
            
            if (nbPersonnes > placesRestantes) {
                nbPersonnesInput.setCustomValidity(`Il ne reste que ${placesRestantes} place(s) pour ce créneau.`);
                nbPersonnesInput.reportValidity();
            } else {
                nbPersonnesInput.setCustomValidity('');
            }
        }
        
        // Écouteurs d'événements
        salleSelect.addEventListener('change', chargerCreneaux);
        
        if (nbPersonnesInput) {
            nbPersonnesInput.addEventListener('change', verifierPlaces);
            creneauSelect.addEventListener('change', verifierPlaces);
        }
        
        // Charger les créneaux si une salle est déjà sélectionnée (retour formulaire)
        if (salleSelect.value) {
            chargerCreneaux();
        }
    }
    
    // ========================================================================
    // 4. GESTION DE LA CHECKBOX BUFFET
    // ========================================================================
    
    /**
     * Active ou désactive la checkbox buffet selon la catégorie sélectionnée.
     * La checkbox n'est activable que si la catégorie a buffet_actif = 1.
     */
    function initBuffetCheckbox() {
        const categorieSelect = document.getElementById('id_categorie');
        const buffetCheckbox = document.getElementById('buffet_jeudi');
        const buffetContainer = document.querySelector('.buffet-container');
        
        if (!categorieSelect || !buffetCheckbox) return;
        
        /**
         * Met à jour l'état de la checkbox buffet
         */
        function updateBuffetState() {
            const selectedOption = categorieSelect.options[categorieSelect.selectedIndex];
            
            // Récupérer l'attribut data-buffet de l'option
            const buffetActif = selectedOption.dataset.buffet === '1';
            
            if (buffetActif) {
                buffetCheckbox.disabled = false;
                if (buffetContainer) {
                    buffetContainer.classList.remove('disabled');
                }
            } else {
                buffetCheckbox.disabled = true;
                buffetCheckbox.checked = false;
                if (buffetContainer) {
                    buffetContainer.classList.add('disabled');
                }
            }
        }
        
        // Écouteur d'événement
        categorieSelect.addEventListener('change', updateBuffetState);
        
        // État initial
        updateBuffetState();
    }
    
    // ========================================================================
    // 5. VALIDATION DES FORMULAIRES
    // ========================================================================
    
    /**
     * Ajoute une validation côté client aux formulaires.
     * Validation basique avant soumission.
     */
    function initFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(function(form) {
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                // Vérifier les champs requis
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(function(field) {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('error');
                        showFieldError(field, 'Ce champ est requis');
                    } else {
                        field.classList.remove('error');
                        hideFieldError(field);
                    }
                });
                
                // Vérifier les emails
                const emailFields = form.querySelectorAll('input[type="email"]');
                emailFields.forEach(function(field) {
                    if (field.value && !isValidEmail(field.value)) {
                        isValid = false;
                        field.classList.add('error');
                        showFieldError(field, 'Adresse email invalide');
                    }
                });
                
                if (!isValid) {
                    event.preventDefault();
                    // Scroll vers la première erreur
                    const firstError = form.querySelector('.error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
            
            // Retirer les erreurs quand l'utilisateur corrige
            const fields = form.querySelectorAll('input, select, textarea');
            fields.forEach(function(field) {
                field.addEventListener('input', function() {
                    field.classList.remove('error');
                    hideFieldError(field);
                });
            });
        });
        
        /**
         * Vérifie si une adresse email est valide
         */
        function isValidEmail(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        }
        
        /**
         * Affiche un message d'erreur sous un champ
         */
        function showFieldError(field, message) {
            let errorElement = field.parentElement.querySelector('.form-error');
            
            if (!errorElement) {
                errorElement = document.createElement('span');
                errorElement.classList.add('form-error');
                field.parentElement.appendChild(errorElement);
            }
            
            errorElement.textContent = message;
        }
        
        /**
         * Masque le message d'erreur d'un champ
         */
        function hideFieldError(field) {
            const errorElement = field.parentElement.querySelector('.form-error');
            if (errorElement) {
                errorElement.remove();
            }
        }
    }
    
    // ========================================================================
    // 6. MESSAGES FLASH
    // ========================================================================
    
    /**
     * Gère la fermeture des messages flash (notifications).
     */
    function initFlashMessages() {
        const flashMessages = document.querySelectorAll('.flash-message');
        
        flashMessages.forEach(function(flash) {
            const closeBtn = flash.querySelector('.flash-close');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    flash.style.animation = 'slideUp 0.3s ease forwards';
                    setTimeout(function() {
                        flash.remove();
                    }, 300);
                });
            }
            
            // Auto-fermeture après 10 secondes
            setTimeout(function() {
                if (flash.parentElement) {
                    flash.style.animation = 'slideUp 0.3s ease forwards';
                    setTimeout(function() {
                        flash.remove();
                    }, 300);
                }
            }, 10000);
        });
    }
    
    // ========================================================================
    // 7. CONFIRMATION DE SUPPRESSION
    // ========================================================================
    
    /**
     * Demande confirmation avant les actions de suppression.
     */
    function initDeleteConfirmation() {
        const deleteButtons = document.querySelectorAll('[data-confirm]');
        
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                const message = button.dataset.confirm || 'Êtes-vous sûr de vouloir effectuer cette action ?';
                
                if (!confirm(message)) {
                    event.preventDefault();
                }
            });
        });
    }
    
    // ========================================================================
    // 8. COMPTEUR DE CARACTÈRES (Textarea)
    // ========================================================================
    
    /**
     * Affiche un compteur de caractères pour les textareas.
     */
    function initCharacterCounter() {
        const textareas = document.querySelectorAll('textarea[maxlength]');
        
        textareas.forEach(function(textarea) {
            const maxLength = parseInt(textarea.getAttribute('maxlength'), 10);
            
            // Créer le compteur
            const counter = document.createElement('span');
            counter.classList.add('form-hint');
            counter.style.textAlign = 'right';
            counter.style.display = 'block';
            
            function updateCounter() {
                const remaining = maxLength - textarea.value.length;
                counter.textContent = `${remaining} caractère(s) restant(s)`;
                
                if (remaining < 50) {
                    counter.style.color = '#af0000';
                } else {
                    counter.style.color = '';
                }
            }
            
            textarea.parentElement.appendChild(counter);
            textarea.addEventListener('input', updateCounter);
            updateCounter();
        });
    }
    
    // ========================================================================
    // INITIALISATION
    // ========================================================================
    
    // Lancer toutes les initialisations
    initBurgerMenu();
    initCarrousel();
    initCreneauxFetch();
    initBuffetCheckbox();
    initFormValidation();
    initFlashMessages();
    initDeleteConfirmation();
    initCharacterCounter();
    
    // Log de confirmation (dev uniquement)
    console.log('E-LLUSION - Scripts initialisés');
});

// ============================================================================
// ANIMATION SUPPLÉMENTAIRE POUR LES MESSAGES FLASH
// ============================================================================
// Ajout dynamique de l'animation slideUp au CSS
(function() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
    `;
    document.head.appendChild(style);
})();
