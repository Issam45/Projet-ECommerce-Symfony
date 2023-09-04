$(document).ready(() => {

    $('.txtTelephone').mask('000-000-0000', { placeholder: '___-___-____' });

    //Masque poue le code postal -> a valider bug majuscule slmt
    $('.txtCodePostal').mask('A0B 0B0', {
        placeholder: '___-___',
        translation: {
            A: { pattern: /[ABCEGHJ-NPRSTVXY]/i },
            B: { pattern: /[ABCEGHJ-NPRSTV-Z]/i }
        }
    });

    $('.txtCodePostal').keyup(function () {
        $(this).val($(this).val().toUpperCase());
    });

    const registrationForm = document.querySelectorAll('.needs-validation-register');

    addValidationToForm(registrationForm);

});

function addValidationToForm(forms) {
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        });
}