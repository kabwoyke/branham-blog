if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}

const revealOnScroll = () => {
    if (!('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
    );

    document.querySelectorAll('[data-reveal]').forEach((element) => {
        if (element.getBoundingClientRect().top < window.innerHeight) {
            return;
        }

        element.classList.add('reveal');
        observer.observe(element);
    });
};

revealOnScroll();
