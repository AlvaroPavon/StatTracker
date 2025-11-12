/**
 * WELCOME SCREEN
 * Pantalla de bienvenida después del login exitoso
 */

class WelcomeScreen {
    constructor(userName, duration = 3000) {
        this.userName = userName;
        this.duration = duration;
        this.screen = null;
    }
    
    show() {
        // Crear pantalla de bienvenida
        this.screen = this.createScreen();
        document.body.appendChild(this.screen);
        
        // Crear partículas de fondo
        this.createParticles();
        
        // Auto cerrar después de la duración especificada
        setTimeout(() => this.hide(), this.duration);
    }
    
    createScreen() {
        const screen = document.createElement('div');
        screen.className = 'welcome-screen';
        
        screen.innerHTML = `
            <div class="welcome-particles">
                <div class="welcome-glow"></div>
                <div class="welcome-glow"></div>
            </div>
            
            <div class="welcome-content">
                <div class="welcome-icon welcome-logo-animation">
                    <span class="material-symbols-outlined">waving_hand</span>
                </div>
                
                <h1 class="welcome-title">¡Bienvenido de nuevo!</h1>
                <h2 class="welcome-username">${this.escapeHtml(this.userName)}</h2>
                <p class="welcome-message">Cargando tu dashboard...</p>
                
                <div class="welcome-loader">
                    <div class="welcome-loader-dot"></div>
                    <div class="welcome-loader-dot"></div>
                    <div class="welcome-loader-dot"></div>
                </div>
            </div>
        `;
        
        return screen;
    }
    
    createParticles() {
        const particlesContainer = this.screen.querySelector('.welcome-particles');
        const particleCount = 30;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'welcome-particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 15 + 's';
            particle.style.animationDuration = (15 + Math.random() * 10) + 's';
            particlesContainer.appendChild(particle);
        }
    }
    
    hide() {
        if (!this.screen) return;
        
        // Añadir clase de fade out
        this.screen.classList.add('fade-out');
        
        // Remover después de la animación
        setTimeout(() => {
            if (this.screen && this.screen.parentNode) {
                this.screen.remove();
            }
        }, 500);
    }
    
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Función para mostrar welcome screen
function showWelcomeScreen(userName, duration = 3000) {
    const welcome = new WelcomeScreen(userName, duration);
    welcome.show();
    return welcome;
}

// Exportar para uso global
window.WelcomeScreen = WelcomeScreen;
window.showWelcomeScreen = showWelcomeScreen;
