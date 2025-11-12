/**
 * CURSOR SPOTLIGHT EFFECT
 * Efecto de sombra/luz que sigue al cursor del mouse
 */

class CursorSpotlight {
    constructor() {
        // Solo activar en desktop
        if (window.innerWidth < 768) return;
        
        this.cursorPos = { x: 0, y: 0 };
        this.spotlightPos = { x: 0, y: 0 };
        this.ringPos = { x: 0, y: 0 };
        this.isHovering = false;
        this.trailTimer = null;
        
        this.init();
    }
    
    init() {
        // Crear elementos del cursor
        this.createCursorElements();
        
        // Event listeners
        document.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        document.addEventListener('mousedown', (e) => this.handleMouseDown(e));
        
        // Detectar elementos interactivos
        this.setupInteractiveElements();
        
        // Activar spotlight en body
        document.body.classList.add('spotlight-enabled');
        
        // Iniciar animación
        this.animate();
    }
    
    createCursorElements() {
        // Overlay oscuro
        this.overlay = document.createElement('div');
        this.overlay.className = 'spotlight-overlay';
        document.body.appendChild(this.overlay);
        
        // Spotlight principal
        this.spotlightMain = document.createElement('div');
        this.spotlightMain.className = 'cursor-spotlight spotlight-main';
        document.body.appendChild(this.spotlightMain);
        
        // Spotlight secundario
        this.spotlightSecondary = document.createElement('div');
        this.spotlightSecondary.className = 'cursor-spotlight spotlight-secondary';
        document.body.appendChild(this.spotlightSecondary);
        
        // Punto del cursor
        this.cursorDot = document.createElement('div');
        this.cursorDot.className = 'cursor-spotlight cursor-dot';
        document.body.appendChild(this.cursorDot);
        
        // Anillo del cursor
        this.cursorRing = document.createElement('div');
        this.cursorRing.className = 'cursor-spotlight cursor-ring';
        document.body.appendChild(this.cursorRing);
    }
    
    handleMouseMove(e) {
        this.cursorPos.x = e.clientX;
        this.cursorPos.y = e.clientY;
        
        // Trail effect
        if (this.trailTimer) clearTimeout(this.trailTimer);
        this.trailTimer = setTimeout(() => this.createTrail(), 50);
    }
    
    handleMouseDown(e) {
        // Crear efecto de partículas al hacer click
        this.createParticles(e.clientX, e.clientY);
    }
    
    createTrail() {
        const trail = document.createElement('div');
        trail.className = 'cursor-trail';
        trail.style.left = this.cursorPos.x + 'px';
        trail.style.top = this.cursorPos.y + 'px';
        document.body.appendChild(trail);
        
        setTimeout(() => trail.remove(), 600);
    }
    
    createParticles(x, y) {
        const particleCount = 8;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'cursor-particle';
            
            const angle = (Math.PI * 2 * i) / particleCount;
            const distance = 50 + Math.random() * 50;
            const tx = Math.cos(angle) * distance;
            const ty = Math.sin(angle) * distance;
            
            particle.style.left = x + 'px';
            particle.style.top = y + 'px';
            particle.style.setProperty('--tx', tx + 'px');
            particle.style.setProperty('--ty', ty + 'px');
            
            document.body.appendChild(particle);
            
            setTimeout(() => particle.remove(), 800);
        }
    }
    
    setupInteractiveElements() {
        const interactiveSelectors = 'a, button, input, textarea, select, [role="button"], .clickable';
        
        document.addEventListener('mouseover', (e) => {
            if (e.target.matches(interactiveSelectors) || e.target.closest(interactiveSelectors)) {
                this.isHovering = true;
                this.spotlightMain.classList.add('active');
                this.cursorDot.classList.add('active');
                this.cursorRing.classList.add('active');
            }
        });
        
        document.addEventListener('mouseout', (e) => {
            if (e.target.matches(interactiveSelectors) || e.target.closest(interactiveSelectors)) {
                this.isHovering = false;
                this.spotlightMain.classList.remove('active');
                this.cursorDot.classList.remove('active');
                this.cursorRing.classList.remove('active');
            }
        });
    }
    
    animate() {
        // Suavizar movimiento con lerp
        this.spotlightPos.x += (this.cursorPos.x - this.spotlightPos.x) * 0.15;
        this.spotlightPos.y += (this.cursorPos.y - this.spotlightPos.y) * 0.15;
        
        this.ringPos.x += (this.cursorPos.x - this.ringPos.x) * 0.1;
        this.ringPos.y += (this.cursorPos.y - this.ringPos.y) * 0.1;
        
        // Actualizar posiciones
        this.spotlightMain.style.left = this.spotlightPos.x + 'px';
        this.spotlightMain.style.top = this.spotlightPos.y + 'px';
        
        this.spotlightSecondary.style.left = this.spotlightPos.x + 'px';
        this.spotlightSecondary.style.top = this.spotlightPos.y + 'px';
        
        this.cursorDot.style.left = this.cursorPos.x + 'px';
        this.cursorDot.style.top = this.cursorPos.y + 'px';
        
        this.cursorRing.style.left = this.ringPos.x + 'px';
        this.cursorRing.style.top = this.ringPos.y + 'px';
        
        requestAnimationFrame(() => this.animate());
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new CursorSpotlight();
    });
} else {
    new CursorSpotlight();
}
