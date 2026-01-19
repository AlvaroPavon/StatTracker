/**
 * SessionTimeout.js - Sistema de cierre automático por inactividad
 * Detecta inactividad del usuario y cierra la sesión automáticamente
 * @package StatTracker
 */

class SessionTimeout {
    constructor(options = {}) {
        // Configuración por defecto (en segundos)
        this.config = {
            idleTimeout: options.idleTimeout || 900,        // 15 minutos de inactividad
            warningTime: options.warningTime || 60,         // Mostrar advertencia 60 segundos antes
            checkInterval: options.checkInterval || 10,     // Verificar cada 10 segundos
            logoutUrl: options.logoutUrl || 'logout.php',
            keepAliveUrl: options.keepAliveUrl || 'keep_alive.php',
            onWarning: options.onWarning || null,
            onLogout: options.onLogout || null,
            onActivity: options.onActivity || null,
            csrfToken: options.csrfToken || window.csrfToken || ''
        };

        // Estado
        this.lastActivity = Date.now();
        this.warningShown = false;
        this.warningModal = null;
        this.countdownInterval = null;
        this.checkIntervalId = null;
        this.isActive = true;

        // Eventos a monitorear
        this.activityEvents = [
            'mousedown', 'mousemove', 'keydown', 'keypress',
            'scroll', 'touchstart', 'click', 'wheel'
        ];

        // Inicializar
        this.init();
    }

    /**
     * Inicializa el sistema
     */
    init() {
        // Registrar eventos de actividad
        this.activityEvents.forEach(event => {
            document.addEventListener(event, () => this.registerActivity(), { passive: true });
        });

        // Detectar cambio de visibilidad (usuario cambia de pestaña)
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                // Verificar inmediatamente cuando vuelve a la pestaña
                this.checkSession();
            }
        });

        // Iniciar verificación periódica
        this.checkIntervalId = setInterval(() => this.checkSession(), this.config.checkInterval * 1000);

        // Crear modal de advertencia
        this.createWarningModal();

        console.log('SessionTimeout: Iniciado con timeout de', this.config.idleTimeout, 'segundos');
    }

    /**
     * Registra actividad del usuario
     */
    registerActivity() {
        if (!this.isActive) return;

        const now = Date.now();
        
        // Solo actualizar si ha pasado al menos 1 segundo desde la última actividad
        if (now - this.lastActivity > 1000) {
            this.lastActivity = now;
            
            // Si había advertencia mostrada, ocultarla
            if (this.warningShown) {
                this.hideWarning();
                this.extendSession();
            }

            // Callback de actividad
            if (typeof this.config.onActivity === 'function') {
                this.config.onActivity();
            }
        }
    }

    /**
     * Verifica el estado de la sesión
     */
    checkSession() {
        if (!this.isActive) return;

        const idleTime = (Date.now() - this.lastActivity) / 1000;
        const timeUntilTimeout = this.config.idleTimeout - idleTime;

        // Si ya pasó el timeout, cerrar sesión
        if (timeUntilTimeout <= 0) {
            this.logout('timeout');
            return;
        }

        // Si está cerca del timeout, mostrar advertencia
        if (timeUntilTimeout <= this.config.warningTime && !this.warningShown) {
            this.showWarning(Math.floor(timeUntilTimeout));
        }
    }

    /**
     * Crea el modal de advertencia
     */
    createWarningModal() {
        // Verificar si ya existe
        if (document.getElementById('session-timeout-modal')) {
            this.warningModal = document.getElementById('session-timeout-modal');
            return;
        }

        const modal = document.createElement('div');
        modal.id = 'session-timeout-modal';
        modal.className = 'session-timeout-modal hidden';
        modal.innerHTML = `
            <div class="session-timeout-backdrop"></div>
            <div class="session-timeout-content">
                <div class="session-timeout-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <h2 class="session-timeout-title">Sesión por expirar</h2>
                <p class="session-timeout-message">
                    Tu sesión se cerrará automáticamente en <span id="timeout-countdown" class="countdown">60</span> segundos por inactividad.
                </p>
                <div class="session-timeout-actions">
                    <button type="button" id="timeout-continue" class="btn-continue">
                        Continuar sesión
                    </button>
                    <button type="button" id="timeout-logout" class="btn-logout">
                        Cerrar sesión ahora
                    </button>
                </div>
            </div>
        `;

        // Añadir estilos
        this.addStyles();

        document.body.appendChild(modal);
        this.warningModal = modal;

        // Event listeners
        document.getElementById('timeout-continue').addEventListener('click', () => {
            this.hideWarning();
            this.extendSession();
        });

        document.getElementById('timeout-logout').addEventListener('click', () => {
            this.logout('user_requested');
        });
    }

    /**
     * Añade estilos CSS
     */
    addStyles() {
        if (document.getElementById('session-timeout-styles')) return;

        const styles = document.createElement('style');
        styles.id = 'session-timeout-styles';
        styles.textContent = `
            .session-timeout-modal {
                position: fixed;
                inset: 0;
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s, visibility 0.3s;
            }
            
            .session-timeout-modal.visible {
                opacity: 1;
                visibility: visible;
            }
            
            .session-timeout-modal.hidden {
                display: none;
            }
            
            .session-timeout-backdrop {
                position: absolute;
                inset: 0;
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(4px);
            }
            
            .session-timeout-content {
                position: relative;
                background: white;
                border-radius: 16px;
                padding: 32px;
                max-width: 400px;
                width: 90%;
                text-align: center;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                animation: modalSlideIn 0.3s ease-out;
            }
            
            .dark .session-timeout-content {
                background: #1f2937;
                color: #f9fafb;
            }
            
            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: scale(0.9) translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }
            
            .session-timeout-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                border-radius: 50%;
                margin-bottom: 20px;
                color: white;
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
            
            .session-timeout-title {
                font-size: 24px;
                font-weight: 700;
                color: #1f2937;
                margin: 0 0 12px 0;
            }
            
            .dark .session-timeout-title {
                color: #f9fafb;
            }
            
            .session-timeout-message {
                font-size: 16px;
                color: #6b7280;
                margin: 0 0 24px 0;
                line-height: 1.5;
            }
            
            .dark .session-timeout-message {
                color: #9ca3af;
            }
            
            .countdown {
                display: inline-block;
                font-size: 24px;
                font-weight: 700;
                color: #dc2626;
                background: #fef2f2;
                padding: 4px 12px;
                border-radius: 8px;
                min-width: 50px;
            }
            
            .dark .countdown {
                background: rgba(220, 38, 38, 0.2);
            }
            
            .session-timeout-actions {
                display: flex;
                gap: 12px;
                justify-content: center;
            }
            
            .session-timeout-actions button {
                padding: 12px 24px;
                border-radius: 10px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                border: none;
            }
            
            .btn-continue {
                background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
                color: white;
            }
            
            .btn-continue:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(74, 144, 226, 0.4);
            }
            
            .btn-logout {
                background: #f3f4f6;
                color: #6b7280;
            }
            
            .dark .btn-logout {
                background: #374151;
                color: #9ca3af;
            }
            
            .btn-logout:hover {
                background: #e5e7eb;
            }
            
            .dark .btn-logout:hover {
                background: #4b5563;
            }
            
            /* Indicador de tiempo en la barra */
            .session-time-indicator {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: rgba(0, 0, 0, 0.7);
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 12px;
                z-index: 9999;
                display: none;
            }
            
            .session-time-indicator.warning {
                background: rgba(245, 158, 11, 0.9);
            }
        `;

        document.head.appendChild(styles);
    }

    /**
     * Muestra la advertencia de timeout
     */
    showWarning(secondsRemaining) {
        if (this.warningShown) return;

        this.warningShown = true;
        this.warningModal.classList.remove('hidden');
        
        // Pequeño delay para la animación
        setTimeout(() => {
            this.warningModal.classList.add('visible');
        }, 10);

        // Iniciar countdown
        this.startCountdown(secondsRemaining);

        // Callback
        if (typeof this.config.onWarning === 'function') {
            this.config.onWarning(secondsRemaining);
        }

        // Sonido de alerta (opcional)
        this.playAlertSound();
    }

    /**
     * Oculta la advertencia
     */
    hideWarning() {
        this.warningShown = false;
        this.warningModal.classList.remove('visible');
        
        setTimeout(() => {
            this.warningModal.classList.add('hidden');
        }, 300);

        // Detener countdown
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
            this.countdownInterval = null;
        }
    }

    /**
     * Inicia el countdown en el modal
     */
    startCountdown(seconds) {
        const countdownEl = document.getElementById('timeout-countdown');
        let remaining = seconds;

        countdownEl.textContent = remaining;

        this.countdownInterval = setInterval(() => {
            remaining--;
            countdownEl.textContent = remaining;

            if (remaining <= 10) {
                countdownEl.style.animation = 'pulse 0.5s infinite';
            }

            if (remaining <= 0) {
                clearInterval(this.countdownInterval);
                this.logout('timeout');
            }
        }, 1000);
    }

    /**
     * Extiende la sesión (ping al servidor)
     */
    async extendSession() {
        this.lastActivity = Date.now();

        try {
            const response = await fetch(this.config.keepAliveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    token: this.config.csrfToken,
                    action: 'extend'
                })
            });

            const data = await response.json();
            
            if (data.success) {
                console.log('SessionTimeout: Sesión extendida');
            } else {
                console.warn('SessionTimeout: Error al extender sesión');
            }
        } catch (error) {
            console.error('SessionTimeout: Error de conexión', error);
        }
    }

    /**
     * Cierra la sesión
     */
    logout(reason = 'timeout') {
        this.isActive = false;
        
        // Limpiar intervalos
        if (this.checkIntervalId) {
            clearInterval(this.checkIntervalId);
        }
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }

        // Callback antes de logout
        if (typeof this.config.onLogout === 'function') {
            this.config.onLogout(reason);
        }

        // Redirigir a logout
        const logoutUrl = `${this.config.logoutUrl}?token=${encodeURIComponent(this.config.csrfToken)}&reason=${reason}`;
        window.location.href = logoutUrl;
    }

    /**
     * Reproduce sonido de alerta (sutil)
     */
    playAlertSound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 440; // La (A4)
            oscillator.type = 'sine';
            gainNode.gain.value = 0.1; // Volumen bajo
            
            oscillator.start();
            oscillator.stop(audioContext.currentTime + 0.2);
        } catch (e) {
            // Audio no disponible, ignorar
        }
    }

    /**
     * Obtiene el tiempo restante en segundos
     */
    getRemainingTime() {
        const idleTime = (Date.now() - this.lastActivity) / 1000;
        return Math.max(0, this.config.idleTimeout - idleTime);
    }

    /**
     * Pausa el sistema de timeout
     */
    pause() {
        this.isActive = false;
        console.log('SessionTimeout: Pausado');
    }

    /**
     * Reanuda el sistema de timeout
     */
    resume() {
        this.isActive = true;
        this.lastActivity = Date.now();
        console.log('SessionTimeout: Reanudado');
    }

    /**
     * Destruye el sistema
     */
    destroy() {
        this.isActive = false;
        
        // Limpiar intervalos
        if (this.checkIntervalId) {
            clearInterval(this.checkIntervalId);
        }
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }

        // Remover modal
        if (this.warningModal) {
            this.warningModal.remove();
        }

        // Remover event listeners
        this.activityEvents.forEach(event => {
            document.removeEventListener(event, () => this.registerActivity());
        });

        console.log('SessionTimeout: Destruido');
    }
}

// Exportar para uso global
window.SessionTimeout = SessionTimeout;
