/**
 * IMAGE PICKER (components/_image_picker.php, an achievement's gallery): shows the picture(s) chosen from the computer.
 * <input type="file" data-image-picker> inside a .c-image-picker → its preview shows the file.
 * <input type="file" multiple data-gallery-add="{list id}"> → each file chosen gets a preview in that list (from the
 * list's <template id="{list id}-photo">, its root marked data-gallery-photo). The box keeps every file chosen so far
 * (choosing again adds to them), so they are all sent with the form; a preview's × (data-gallery-remove) takes its
 * file out again.
 */
(function ()
{
  'use strict';

  const chosen = new WeakMap();   // a gallery box → the files it will send, in order
  const fileOf = new WeakMap();   // a new photo's preview → its file

  const showFile = (file, image) =>
  {
    image.src = URL.createObjectURL(file);
    image.hidden = false;
  };

  // The box sends exactly these files (a file box can only be given a list through DataTransfer)
  function setFiles(input, files)
  {
    const list = new DataTransfer();
    files.forEach((file) => list.items.add(file));
    input.files = list.files;
    chosen.set(input, files);
  }

  document.addEventListener('change', (event) =>
  {
    const input = event.target;

    if (input.matches('[data-image-picker]') && input.files[0])
    {
      const picker = input.closest('.c-image-picker');
      showFile(input.files[0], picker.querySelector('.c-image-picker__image'));
      picker.querySelector('.c-image-picker__empty').hidden = true;
    }

    if (input.matches('[data-gallery-add]'))
    {
      const list     = document.getElementById(input.dataset.galleryAdd);
      const template = document.getElementById(`${input.dataset.galleryAdd}-photo`);
      const added    = [...input.files];

      added.forEach((file) =>
      {
        const photo = template.content.firstElementChild.cloneNode(true);
        showFile(file, photo.querySelector('img'));
        fileOf.set(photo, file);
        list.insertBefore(photo, input.closest('label'));
      });
      setFiles(input, [...(chosen.get(input) ?? []), ...added]);
    }
  });

  document.addEventListener('click', (event) =>
  {
    const remove = event.target.closest('[data-gallery-remove]');

    if (!remove) return;

    const photo = remove.closest('[data-gallery-photo]');
    const input = photo.parentElement.querySelector('[data-gallery-add]');
    setFiles(input, (chosen.get(input) ?? []).filter((file) => file !== fileOf.get(photo)));
    photo.remove();
  });
})();
