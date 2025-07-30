const radioButtons = document.querySelectorAll('input[name="input_type"]');
const passwordInput = document.getElementById("password");
const emailContainer = document.getElementById('email_container');


radioButtons.forEach(radio => {
    updateEmail(radio);
    radio.addEventListener('change', () => updateEmail(radio));
});

function updateEmail(radio) {
    if (radio.checked) {
        const selectedValue = radio.value;

        if (selectedValue === 'login') {
            passwordInput.setAttribute('autocomplete', 'current-password');
            emailContainer.style.display = 'none';
        } else if (selectedValue === 'register') {
            passwordInput.setAttribute('autocomplete', 'new-password');
            emailContainer.style.display = '';
        }
    }
}