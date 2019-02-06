import '../scss/styles.scss';
import InputfieldGooglePreview from "./components/InputfieldGooglePreview";

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-seomaestro-googlepreview]').forEach(($elem) => {
        const googlePreview = new InputfieldGooglePreview($elem, $elem.dataset.seomaestroGooglepreview);
        googlePreview.init();
    });

    const inheritWrappers = document.querySelectorAll('[data-seomaestro-metadata-inherit]');

    inheritWrappers.forEach(($wrapper) => {
        $wrapper.addEventListener('change', (event) => {
            if (event.target.checked) {
                return;
            }

            const inputName = $wrapper.dataset.seomaestroMetadataInherit;
            const $input = document.querySelector(`input[name="${inputName}"], textarea[name="${inputName}"]`);
            if ($input) {
                $input.focus();
            }
        });
    });
});
