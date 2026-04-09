const navToggle = document.querySelector('.nav-toggle');
const navMenu = document.querySelector('.nav-menu');
const logoShowcase = document.querySelector('.logo-showcase');
const formAlert = document.querySelector('#formAlert');

if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
        const isOpen = navMenu.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    navMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('open');
            navToggle.setAttribute('aria-expanded', 'false');
        });
    });
}

if (logoShowcase) {
    const toggleFlip = () => {
        logoShowcase.classList.toggle('is-flipped');
    };

    logoShowcase.addEventListener('click', (event) => {
        // On touch devices, emulate the hover flip without getting stuck.
        event.preventDefault();
        toggleFlip();
    });

    logoShowcase.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            toggleFlip();
        }
    });

    document.addEventListener('click', (event) => {
        if (!logoShowcase.contains(event.target)) {
            logoShowcase.classList.remove('is-flipped');
        }
    });
}

if (formAlert) {
    const params = new URLSearchParams(window.location.search);
    const status = params.get('status');

    if (status === 'success') {
        formAlert.textContent = 'Your request has been sent successfully. Our team will contact you shortly.';
        formAlert.classList.add('success');
        formAlert.hidden = false;
    }

    if (status === 'error') {
        formAlert.textContent = 'We could not send your request. Please review the form and try again.';
        formAlert.classList.add('error');
        formAlert.hidden = false;
    }
}
