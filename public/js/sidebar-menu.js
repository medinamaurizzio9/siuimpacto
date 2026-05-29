document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sidebar-accordion]').forEach((accordion) => {
        accordion.querySelectorAll('[data-menu-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                const group = toggle.closest('.sidebar-group');
                if (!group) return;

                const isOpen = group.classList.toggle('open');
                group.classList.toggle('active', isOpen || Boolean(group.querySelector('.sidebar-link.active')));
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });
    });
});
