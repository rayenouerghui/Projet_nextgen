
function togglePassword() {
    const field = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}


function clearErrors() {
    document.querySelectorAll('.error-msg').forEach(el => el.remove());
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
}


function showError(input, message) {
    clearErrors(); 

    const error = document.createElement('small');
    error.className = 'text-danger error-msg d-block mt-1';
    error.textContent = message;

    const inputGroup = input.closest('.input-group');
    inputGroup.parentElement.appendChild(error);
    input.classList.add('is-invalid');
}


document.getElementById('loginForm').addEventListener('submit', function (e) {
    clearErrors();
    let valid = true;

    const email = document.querySelector('input[name="email"]');
    const password = document.getElementById('password');

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    
    if (!email.value.trim()) {
        showError(email, "L'email est obligatoire");
        valid = false;
    } else if (!emailRegex.test(email.value.trim())) {
        showError(email, "Veuillez entrer un email valide");
        valid = false;
    }

    
    if (!password.value.trim()) {
        showError(password, "Le mot de passe est obligatoire");
        valid = false;
    }

    
    if (!valid) {
        e.preventDefault();
    }
});