/**
 * StatTracker - Validación de Formularios del Lado del Cliente
 * Proporciona validación en tiempo real para mejorar UX
 * La validación principal siempre se hace en el servidor
 */

const FormValidation = {
    // Constantes de validación (deben coincidir con Security.php)
    MAX_NOMBRE: 50,
    MAX_APELLIDOS: 100,
    MAX_EMAIL: 255,
    MIN_PASSWORD: 8,
    MAX_PASSWORD: 72,
    MIN_ALTURA: 0.50,
    MAX_ALTURA: 2.50,
    MIN_PESO: 1.0,
    MAX_PESO: 500.0,

    // Patrones de validación
    PATTERN_NOMBRE: /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/,
    PATTERN_EMAIL: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    
    /**
     * Valida un campo de nombre
     */
    validateNombre: function(value) {
        value = value.trim();
        if (!value) return { valid: false, message: 'El nombre es obligatorio.' };
        if (value.length > this.MAX_NOMBRE) return { valid: false, message: `Máximo ${this.MAX_NOMBRE} caracteres.` };
        if (!this.PATTERN_NOMBRE.test(value)) return { valid: false, message: 'Solo letras, espacios y guiones.' };
        return { valid: true, message: '' };
    },

    /**
     * Valida un campo de apellidos
     */
    validateApellidos: function(value) {
        value = value.trim();
        if (!value) return { valid: false, message: 'Los apellidos son obligatorios.' };
        if (value.length > this.MAX_APELLIDOS) return { valid: false, message: `Máximo ${this.MAX_APELLIDOS} caracteres.` };
        if (!this.PATTERN_NOMBRE.test(value)) return { valid: false, message: 'Solo letras, espacios y guiones.' };
        return { valid: true, message: '' };
    },

    /**
     * Valida un campo de email
     */
    validateEmail: function(value) {
        value = value.trim().toLowerCase();
        if (!value) return { valid: false, message: 'El email es obligatorio.' };
        if (value.length > this.MAX_EMAIL) return { valid: false, message: `Máximo ${this.MAX_EMAIL} caracteres.` };
        if (!this.PATTERN_EMAIL.test(value)) return { valid: false, message: 'Formato de email inválido.' };
        return { valid: true, message: '' };
    },

    /**
     * Valida un campo de contraseña
     */
    validatePassword: function(value) {
        if (!value) return { valid: false, message: 'La contraseña es obligatoria.' };
        if (value.length < this.MIN_PASSWORD) return { valid: false, message: `Mínimo ${this.MIN_PASSWORD} caracteres.` };
        if (value.length > this.MAX_PASSWORD) return { valid: false, message: `Máximo ${this.MAX_PASSWORD} caracteres.` };
        if (!/[a-z]/.test(value)) return { valid: false, message: 'Debe contener al menos una minúscula.' };
        if (!/[A-Z]/.test(value)) return { valid: false, message: 'Debe contener al menos una mayúscula.' };
        if (!/[0-9]/.test(value)) return { valid: false, message: 'Debe contener al menos un número.' };
        return { valid: true, message: '' };
    },

    /**
     * Valida un campo de altura
     */
    validateAltura: function(value) {
        const altura = parseFloat(value);
        if (isNaN(altura)) return { valid: false, message: 'La altura debe ser un número.' };
        if (altura < this.MIN_ALTURA || altura > this.MAX_ALTURA) {
            return { valid: false, message: `La altura debe estar entre ${this.MIN_ALTURA} y ${this.MAX_ALTURA} metros.` };
        }
        return { valid: true, message: '' };
    },

    /**
     * Valida un campo de peso
     */
    validatePeso: function(value) {
        const peso = parseFloat(value);
        if (isNaN(peso)) return { valid: false, message: 'El peso debe ser un número.' };
        if (peso < this.MIN_PESO || peso > this.MAX_PESO) {
            return { valid: false, message: `El peso debe estar entre ${this.MIN_PESO} y ${this.MAX_PESO} kg.` };
        }
        return { valid: true, message: '' };
    },

    /**
     * Valida un campo de fecha (no futura)
     */
    validateFecha: function(value) {
        if (!value) return { valid: false, message: 'La fecha es obligatoria.' };
        
        const fecha = new Date(value);
        const hoy = new Date();
        hoy.setHours(23, 59, 59, 999);
        
        if (isNaN(fecha.getTime())) return { valid: false, message: 'Formato de fecha inválido.' };
        if (fecha > hoy) return { valid: false, message: 'La fecha no puede ser futura.' };
        
        return { valid: true, message: '' };
    },

    /**
     * Muestra un mensaje de error en un campo
     */
    showError: function(input, message) {
        const container = input.closest('label') || input.parentElement;
        let errorEl = container.querySelector('.validation-error');
        
        if (!errorEl) {
            errorEl = document.createElement('p');
            errorEl.className = 'validation-error text-xs text-red-500 mt-1';
            container.appendChild(errorEl);
        }
        
        errorEl.textContent = message;
        input.classList.add('border-red-500');
        input.classList.remove('border-green-500');
    },

    /**
     * Muestra éxito en un campo
     */
    showSuccess: function(input) {
        const container = input.closest('label') || input.parentElement;
        const errorEl = container.querySelector('.validation-error');
        
        if (errorEl) {
            errorEl.remove();
        }
        
        input.classList.remove('border-red-500');
        input.classList.add('border-green-500');
    },

    /**
     * Limpia validación de un campo
     */
    clearValidation: function(input) {
        const container = input.closest('label') || input.parentElement;
        const errorEl = container.querySelector('.validation-error');
        
        if (errorEl) {
            errorEl.remove();
        }
        
        input.classList.remove('border-red-500', 'border-green-500');
    },

    /**
     * Inicializa validación en tiempo real para un formulario
     */
    initForm: function(formSelector, validations) {
        const form = document.querySelector(formSelector);
        if (!form) return;

        Object.keys(validations).forEach(fieldName => {
            const input = form.querySelector(`[name="${fieldName}"]`);
            if (!input) return;

            const validatorName = validations[fieldName];
            const validator = this[validatorName];
            if (!validator) return;

            // Validar al salir del campo
            input.addEventListener('blur', () => {
                const result = validator.call(this, input.value);
                if (result.valid) {
                    this.showSuccess(input);
                } else {
                    this.showError(input, result.message);
                }
            });

            // Limpiar al escribir
            input.addEventListener('input', () => {
                this.clearValidation(input);
            });
        });

        // Validar todo antes de enviar
        form.addEventListener('submit', (e) => {
            let isValid = true;
            
            Object.keys(validations).forEach(fieldName => {
                const input = form.querySelector(`[name="${fieldName}"]`);
                if (!input) return;

                const validatorName = validations[fieldName];
                const validator = this[validatorName];
                if (!validator) return;

                const result = validator.call(this, input.value);
                if (!result.valid) {
                    this.showError(input, result.message);
                    isValid = false;
                } else {
                    this.showSuccess(input);
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
};

// Auto-inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Formulario de registro
    FormValidation.initForm('form[action="register.php"]', {
        'nombre': 'validateNombre',
        'apellidos': 'validateApellidos',
        'email': 'validateEmail',
        'password': 'validatePassword'
    });

    // Formulario de login
    FormValidation.initForm('form[action="login.php"]', {
        'email': 'validateEmail'
    });

    // Formulario de actualización de perfil
    FormValidation.initForm('form[action="update_profile.php"]', {
        'nombre': 'validateNombre',
        'apellidos': 'validateApellidos',
        'email': 'validateEmail'
    });

    // Formulario de cambio de contraseña
    FormValidation.initForm('form[action="change_password.php"]', {
        'new_password': 'validatePassword'
    });

    // Formulario de registro de métricas
    FormValidation.initForm('#form-registro', {
        'altura': 'validateAltura',
        'peso': 'validatePeso',
        'fecha_registro': 'validateFecha'
    });
});
