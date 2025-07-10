document.addEventListener('DOMContentLoaded', () => {

    const nav = document.querySelector("header nav");
    const open = document.querySelector("header nav .menu-open");
    const close = document.querySelector("header nav .menu-close");

    open?.addEventListener("click", () => {
        nav?.querySelectorAll(".menu .is-menu")?.forEach(el => el.classList.toggle('dropdown'));
        nav?.classList?.toggle("small");
        open?.classList?.toggle("show");
        close?.classList?.toggle("show");
    });

    close?.addEventListener("click", () => {
        nav?.querySelectorAll(".menu .is-menu")?.forEach(el => el.classList.toggle('dropdown'));
        nav?.classList?.toggle("small");
        open?.classList?.toggle("show");
        close?.classList?.toggle("show");
    });
});
