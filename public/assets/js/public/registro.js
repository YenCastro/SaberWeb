// Lógica para mostrar/ocultar contraseña
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

togglePassword.addEventListener('click', () => {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    togglePassword.classList.toggle('fa-eye');
    togglePassword.classList.toggle('fa-eye-slash');
});

// Validación de contraseña en tiempo real
const passwordHint = document.getElementById('passwordHint');
const form = document.querySelector('.auth-form');

function validarPassword(password) {
    const tieneMinimo8 = password.length >= 8;
    const tieneMayuscula = /[A-Z]/.test(password);
    const tieneNumero = /[0-9]/.test(password);

    return {
        valido: tieneMinimo8 && tieneMayuscula && tieneNumero,
        tieneMinimo8,
        tieneMayuscula,
        tieneNumero
    };
}

function actualizarHint(resultado) {
    passwordHint.classList.remove('valido', 'invalido');

    if (resultado.valido) {
        passwordHint.classList.add('valido');
        passwordHint.textContent = '✓ Contraseña segura';
    } else {
        passwordHint.classList.add('invalido');
        const faltantes = [];
        if (!resultado.tieneMinimo8) faltantes.push('mínimo 8 caracteres');
        if (!resultado.tieneMayuscula) faltantes.push('una mayúscula');
        if (!resultado.tieneNumero) faltantes.push('un número');
        passwordHint.textContent = 'Falta: ' + faltantes.join(', ');
    }
}

passwordInput.addEventListener('input', () => {
    actualizarHint(validarPassword(passwordInput.value));
});

form.addEventListener('submit', (e) => {
    const resultado = validarPassword(passwordInput.value);
    if (!resultado.valido) {
        e.preventDefault();
        passwordHint.classList.remove('valido');
        passwordHint.classList.add('invalido');
        passwordHint.textContent = 'La contraseña no cumple los requisitos mínimos.';
    }
});