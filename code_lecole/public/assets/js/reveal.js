// Fades in every [data-reveal] element when it scrolls into view (the looks are in global.css)
// Wrapped in { } so "observer" stays private to this file and can't clash with other scripts
{
    const observer = new IntersectionObserver((entries) =>
    {
        entries.forEach((entry) =>
        {
            if (entry.isIntersecting)
            {
                entry.target.classList.add('is-visible');   // CSS fades it in
                observer.unobserve(entry.target);            // only animate once
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('[data-reveal]').forEach((item) => observer.observe(item));
}
