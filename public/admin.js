/**
 * Allow editing content by admins in preview
 */

if (window.self !== window.top) {
    let trustedOrigin

    // Handle messages from parent window
    window.addEventListener('message', msg => {
        if(trustedOrigin && trustedOrigin !== msg.origin) return

        switch(msg.data) {
            case 'init': trustedOrigin = msg.origin; break
            case 'reload': location.reload(); break
        }
    });


    document.addEventListener('DOMContentLoaded', () => {

        // Show actions menu
        document.querySelectorAll('.cms-content').forEach(el => {
            const section = el.dataset.section || 'main'

            if(!el.children.length) {
                el.classList.add('placeholder')
            }

            el.addEventListener('dblclick', ev => {
                const id = ev.target?.closest('[id]')?.id || -1;
                window.parent.postMessage({id: id, section: section}, trustedOrigin || '*');
            });
        });


        // Prevent off-page link actions in preview mode or hide actions menu
        document.body.addEventListener('click', (e) => {
            const link = e.target.closest('a')?.href;

            if(link) {
                const current = window.location;
                const target = new URL(link, current);

                if(target.origin !== current.origin || target.pathname !== current.pathname) {
                    window.parent.postMessage(false, trustedOrigin || '*');
                    e.stopPropagation();
                    e.preventDefault();
                }
                return;
            }

            window.parent.postMessage(null, trustedOrigin || '*');
        });

        // Prevent native form submissions in preview mode
        document.body.addEventListener('submit', (e) => {
            window.parent.postMessage(false, trustedOrigin || '*');
            e.stopPropagation();
            e.preventDefault();
        });
    });
}
