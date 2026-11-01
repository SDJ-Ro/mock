// Small extras for the sign-in pages. The pages still work without this file.
// (The password eye: components/password-field.js)
{
    // Chooser: hovering a role shows that role's photo
    const photos = document.querySelectorAll('[data-role-image]');

    const showPhoto = (role) =>
    {
        photos.forEach((photo) => photo.classList.toggle('is-active', photo.dataset.roleImage === role));
    };

    document.querySelectorAll('.auth-role').forEach((link) =>
    {
        link.addEventListener('mouseenter', () => showPhoto(link.dataset.role));
        link.addEventListener('mouseleave', () => showPhoto(''));   // '' = the collage
    });

    // Demo button: fills the form with a test account
    document.querySelectorAll('.auth-demo').forEach((button) =>
    {
        button.addEventListener('click', () =>
        {
            const form = document.querySelector('.auth-form');
            form.identifier.value = button.dataset.identifier;
            form.password.value = button.dataset.password;
        });
    });

    // (The "Forgot password?" pop-up needs no code here: components/pop-up.js opens and closes it)
}

