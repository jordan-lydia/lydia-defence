document.addEventListener('DOMContentLoaded', function() {

    // 1. Initialisation de AOS (Animate On Scroll)
    AOS.init({
        duration: 800,      // Durée de l'animation
        easing: 'ease-in-out', // Courbe d'animation
        once: true          // L'animation ne se joue qu'une seule fois
    });

    // 2. Gestion de la barre de navigation au défilement
    const navbar = document.getElementById('mainNav');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // 3. Animation du compteur de statistiques
    const counters = document.querySelectorAll('.counter');
    const speed = 200; // Vitesse de base de l'animation

    const animateCounter = (counter) => {
        const target = +counter.getAttribute('data-count');
        let count = 0;

        const updateCount = () => {
            const increment = target / speed;
            count += increment;

            if (count < target) {
                counter.innerText = Math.ceil(count).toLocaleString('fr-FR');
                requestAnimationFrame(updateCount);
            } else {
                counter.innerText = target.toLocaleString('fr-FR');
            }
        };
        updateCount();
    };
    
    // Utilisation de IntersectionObserver pour déclencher l'animation au bon moment
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target); // Ne l'anime qu'une fois
            }
        });
    }, { threshold: 0.5 }); // Déclenche quand 50% de l'élément est visible

    counters.forEach(counter => {
        observer.observe(counter);
    });

    // 4. Initialisation de Particles.js avec une configuration moderne
    particlesJS('particles-js', {
        "particles": {
            "number": {
                "value": 60,
                "density": {
                    "enable": true,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#00A79D"
            },
            "shape": {
                "type": "circle",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                }
            },
            "opacity": {
                "value": 0.4,
                "random": true,
                "anim": {
                    "enable": true,
                    "speed": 1,
                    "opacity_min": 0.1,
                    "sync": false
                }
            },
            "size": {
                "value": 3,
                "random": true,
                "anim": {
                    "enable": false
                }
            },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#ffffff",
                "opacity": 0.1,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 2,
                "direction": "none",
                "random": false,
                "straight": false,
                "out_mode": "out",
                "bounce": false
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": true,
                    "mode": "grab"
                },
                "onclick": {
                    "enable": true,
                    "mode": "push"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 140,
                    "line_linked": {
                        "opacity": 0.3
                    }
                },
                "push": {
                    "particles_nb": 4
                }
            }
        },
        "retina_detect": true
    });

});