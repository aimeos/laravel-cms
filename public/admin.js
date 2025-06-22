/**
 * Allow editing content by admins in preview
 */

if (window.self !== window.top) {
    document.addEventListener('DOMContentLoaded', () => {
        let trustedOrigin

        // Handle messages from parent window
        window.addEventListener('message', msg => {
            if(trustedOrigin && trustedOrigin !== msg.origin) return

            switch(msg.data) {
                case 'init': trustedOrigin = msg.origin; break
                case 'reload': location.reload(); break
            }
        });


        // Show actions menu
        document.querySelectorAll('.cms-content').forEach(el => {
            el.addEventListener('dblclick', ev => {
                const node = ev.target?.closest('[id]');
                if(node?.id) {
                    window.parent.postMessage(node.id, trustedOrigin || '*');
                }
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
