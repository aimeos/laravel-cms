/**
 * Send contact form
 */
document.querySelectorAll('.contact form').forEach(form => {
    form.addEventListener('submit', e => {
        e.preventDefault();

        form.querySelector('.btn').disabled = true;
        form.querySelector('.btn .send')?.classList?.add('hidden');
        form.querySelector('.btn .sending')?.classList?.remove('hidden');

        fetch(form.getAttribute('action'), {
            method: 'POST',
            body: new FormData(e.target)
        }).then(response => {
            if(!response.ok) {
                throw new Error(response.statusText)
            }

            return response.json();
        }).then(result => {
            if(!result.status) {
                throw new Error(result.message)
            }

            form.querySelector('.btn .success')?.classList?.remove('hidden');
        }).catch(err => {
            form.querySelector('.btn .failure')?.classList?.remove('hidden');
        }).finally(() => {
            form.querySelector('.btn .sending')?.classList?.add('hidden');

            setTimeout(() => {
                form.querySelectorAll('.btn span').forEach(el => {
                    el.classList?.add('hidden');
                });
                form.querySelector('.btn .send')?.classList?.remove('hidden');
                form.querySelector('.btn').disabled = false;
            }, 10000);
        });
    })
});