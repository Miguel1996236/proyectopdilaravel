/**
 * JavaScript para manejar diferentes tipos de preguntas
 * Funcionalidades interactivas para formularios avanzados
 */

class QuestionTypeManager {
    constructor() {
        this.initializeEventListeners();
        this.initializeDragAndDrop();
        this.initializeStarRating();
        this.initializeMatching();
        this.initializeSortable();
    }
    
    initializeEventListeners() {
        // Eventos para opciones mejoradas
        document.addEventListener('click', (e) => {
            if (e.target.closest('.enhanced-options .form-check')) {
                this.handleEnhancedOptionClick(e.target.closest('.form-check'));
            }
            
            if (e.target.closest('.true-false-option')) {
                this.handleTrueFalseClick(e.target.closest('.true-false-option'));
            }
        });
        
        // Eventos para rango numérico
        document.addEventListener('input', (e) => {
            if (e.target.type === 'range') {
                this.updateRangeValue(e.target);
            }
        });
        
        // Eventos para completar espacios
        document.addEventListener('input', (e) => {
            if (e.target.closest('.fill-blanks-container input')) {
                this.handleFillInBlanksInput(e.target);
            }
        });
    }
    
    /**
     * Maneja clics en opciones mejoradas
     */
    handleEnhancedOptionClick(element) {
        const input = element.querySelector('.form-check-input');
        if (input) {
            input.checked = !input.checked;
            this.updateEnhancedOptionStyle(element);
        }
    }
    
    /**
     * Actualiza el estilo de opciones mejoradas
     */
    updateEnhancedOptionStyle(element) {
        const input = element.querySelector('.form-check-input');
        if (input.checked) {
            element.classList.add('selected');
        } else {
            element.classList.remove('selected');
        }
    }
    
    /**
     * Maneja clics en opciones verdadero/falso
     */
    handleTrueFalseClick(element) {
        const container = element.closest('.true-false-options');
        const inputs = container.querySelectorAll('input[type="radio"]');
        const options = container.querySelectorAll('.true-false-option');
        
        // Remover selección previa
        options.forEach(option => option.classList.remove('selected'));
        
        // Agregar selección actual
        element.classList.add('selected');
        
        // Actualizar input
        const input = element.querySelector('input[type="radio"]');
        if (input) {
            input.checked = true;
        }
    }
    
    /**
     * Actualiza el valor mostrado en rangos numéricos
     */
    updateRangeValue(rangeInput) {
        const valueDisplay = document.getElementById('range_value_' + rangeInput.name.split('_')[1]);
        if (valueDisplay) {
            valueDisplay.textContent = rangeInput.value;
        }
    }
    
    /**
     * Maneja entrada en campos de completar espacios
     */
    handleFillInBlanksInput(input) {
        // Validar longitud mínima
        if (input.value.length < 2) {
            input.style.borderBottomColor = '#dc3545';
        } else {
            input.style.borderBottomColor = '#28a745';
        }
    }
    
    /**
     * Inicializa funcionalidad de arrastrar y soltar para ordenamiento
     */
    initializeDragAndDrop() {
        document.querySelectorAll('.sortable-list').forEach(list => {
            this.makeSortable(list);
        });
    }
    
    /**
     * Hace una lista ordenable
     */
    makeSortable(container) {
        let draggedElement = null;
        
        container.addEventListener('dragstart', (e) => {
            draggedElement = e.target;
            e.target.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        
        container.addEventListener('dragend', (e) => {
            e.target.classList.remove('dragging');
            draggedElement = null;
        });
        
        container.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            const afterElement = this.getDragAfterElement(container, e.clientY);
            const placeholder = container.querySelector('.sortable-placeholder');
            
            if (afterElement == null) {
                container.appendChild(placeholder);
            } else {
                container.insertBefore(placeholder, afterElement);
            }
        });
        
        container.addEventListener('drop', (e) => {
            e.preventDefault();
            
            if (draggedElement && draggedElement !== e.target) {
                const placeholder = container.querySelector('.sortable-placeholder');
                container.insertBefore(draggedElement, placeholder);
            }
            
            // Actualizar orden
            this.updateSortableOrder(container);
            
            // Remover placeholder
            const placeholder = container.querySelector('.sortable-placeholder');
            if (placeholder) {
                placeholder.remove();
            }
        });
    }
    
    /**
     * Obtiene el elemento después del cual se debe insertar
     */
    getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.sortable-item:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    
    /**
     * Actualiza el orden de elementos ordenables
     */
    updateSortableOrder(container) {
        const preguntaId = container.dataset.pregunta;
        const items = container.querySelectorAll('.sortable-item');
        const order = Array.from(items).map(item => item.dataset.id);
        
        const hiddenInput = document.getElementById('orden_' + preguntaId);
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(order);
        }
    }
    
    /**
     * Inicializa calificación con estrellas
     */
    initializeStarRating() {
        document.querySelectorAll('.star-rating').forEach(rating => {
            this.setupStarRating(rating);
        });
    }
    
    /**
     * Configura calificación con estrellas
     */
    setupStarRating(container) {
        const stars = container.querySelectorAll('.star');
        const preguntaId = container.dataset.pregunta;
        let currentRating = 0;
        
        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                currentRating = index + 1;
                this.updateStarRating(stars, currentRating);
                this.updateStarRatingValue(preguntaId, currentRating);
            });
            
            star.addEventListener('mouseenter', () => {
                this.updateStarRating(stars, index + 1);
            });
        });
        
        container.addEventListener('mouseleave', () => {
            this.updateStarRating(stars, currentRating);
        });
    }
    
    /**
     * Actualiza la visualización de estrellas
     */
    updateStarRating(stars, rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
                star.classList.remove('far');
                star.classList.add('fas');
            } else {
                star.classList.remove('active');
                star.classList.remove('fas');
                star.classList.add('far');
            }
        });
    }
    
    /**
     * Actualiza el valor de calificación
     */
    updateStarRatingValue(preguntaId, rating) {
        const hiddenInput = document.getElementById('rating_' + preguntaId);
        if (hiddenInput) {
            hiddenInput.value = rating;
        }
    }
    
    /**
     * Inicializa funcionalidad de relacionar elementos
     */
    initializeMatching() {
        document.querySelectorAll('.matching-container').forEach(container => {
            this.setupMatching(container);
        });
    }
    
    /**
     * Configura funcionalidad de relacionar
     */
    setupMatching(container) {
        let selectedItem = null;
        const connections = [];
        
        container.querySelectorAll('.matching-item').forEach(item => {
            item.addEventListener('click', () => {
                if (selectedItem === null) {
                    // Seleccionar primer elemento
                    selectedItem = item;
                    item.classList.add('selected');
                } else if (selectedItem === item) {
                    // Deseleccionar
                    selectedItem.classList.remove('selected');
                    selectedItem = null;
                } else {
                    // Crear conexión
                    this.createMatchingConnection(selectedItem, item, connections);
                    
                    // Limpiar selección
                    selectedItem.classList.remove('selected');
                    selectedItem = null;
                }
            });
        });
    }
    
    /**
     * Crea una conexión entre elementos
     */
    createMatchingConnection(item1, item2, connections) {
        // Verificar si ya están conectados
        if (item1.classList.contains('matched') || item2.classList.contains('matched')) {
            return;
        }
        
        // Verificar si son de columnas diferentes
        const column1 = item1.closest('.matching-column');
        const column2 = item2.closest('.matching-column');
        
        if (column1 === column2) {
            return;
        }
        
        // Marcar como conectados
        item1.classList.add('matched');
        item2.classList.add('matched');
        
        // Crear línea visual de conexión
        this.drawConnectionLine(item1, item2);
        
        // Actualizar input oculto
        this.updateMatchingConnections();
    }
    
    /**
     * Dibuja línea de conexión visual
     */
    drawConnectionLine(item1, item2) {
        const rect1 = item1.getBoundingClientRect();
        const rect2 = item2.getBoundingClientRect();
        
        const line = document.createElement('div');
        line.className = 'matching-connection';
        line.style.left = Math.min(rect1.left, rect2.left) + 'px';
        line.style.top = (Math.min(rect1.top, rect2.top) + Math.max(rect1.bottom, rect2.bottom)) / 2 + 'px';
        line.style.width = Math.abs(rect2.left - rect1.left) + 'px';
        
        document.body.appendChild(line);
    }
    
    /**
     * Actualiza las conexiones en el input oculto
     */
    updateMatchingConnections() {
        const containers = document.querySelectorAll('.matching-container');
        
        containers.forEach(container => {
            const connections = [];
            const leftItems = container.querySelectorAll('.matching-column:first-child .matching-item.matched');
            
            leftItems.forEach(leftItem => {
                const rightItem = container.querySelector('.matching-column:last-child .matching-item.matched');
                if (rightItem) {
                    connections.push({
                        izquierda: leftItem.dataset.id,
                        derecha: rightItem.dataset.id
                    });
                }
            });
            
            const hiddenInput = container.querySelector('input[type="hidden"]');
            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(connections);
            }
        });
    }
    
    /**
     * Inicializa funcionalidad de ordenamiento
     */
    initializeSortable() {
        // Ya se maneja en initializeDragAndDrop
    }
    
    /**
     * Valida formulario antes del envío
     */
    validateQuestionForm(form) {
        const errors = [];
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                errors.push(`El campo ${field.name} es requerido`);
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Validaciones específicas por tipo
        const likertInputs = form.querySelectorAll('.likert-scale input[type="radio"]');
        if (likertInputs.length > 0) {
            const checkedLikert = form.querySelector('.likert-scale input[type="radio"]:checked');
            if (!checkedLikert) {
                errors.push('Debe seleccionar una opción en la escala Likert');
            }
        }
        
        const starRatings = form.querySelectorAll('.star-rating');
        starRatings.forEach(rating => {
            const hiddenInput = rating.querySelector('input[type="hidden"]');
            if (hiddenInput && !hiddenInput.value) {
                errors.push('Debe seleccionar una calificación con estrellas');
            }
        });
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }
    
    /**
     * Muestra retroalimentación inmediata
     */
    showFeedback(element, message, type = 'info') {
        const feedbackContainer = element.querySelector('.feedback-container') || this.createFeedbackContainer(element);
        
        feedbackContainer.className = `feedback-container ${type}`;
        feedbackContainer.innerHTML = `
            <i class="fas fa-${this.getFeedbackIcon(type)}"></i>
            ${message}
        `;
        
        feedbackContainer.style.display = 'block';
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            feedbackContainer.style.display = 'none';
        }, 5000);
    }
    
    /**
     * Crea contenedor de retroalimentación
     */
    createFeedbackContainer(element) {
        const container = document.createElement('div');
        container.className = 'feedback-container';
        container.style.display = 'none';
        element.appendChild(container);
        return container;
    }
    
    /**
     * Obtiene icono para tipo de retroalimentación
     */
    getFeedbackIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
    
    /**
     * Calcula puntuación en tiempo real
     */
    calculateRealTimeScore(form) {
        let totalScore = 0;
        let maxScore = 0;
        
        // Calcular puntuación de opciones múltiples
        const multipleChoiceInputs = form.querySelectorAll('input[type="radio"]:checked');
        multipleChoiceInputs.forEach(input => {
            const option = input.closest('.form-check');
            const scoreElement = option.querySelector('.score');
            if (scoreElement) {
                totalScore += parseFloat(scoreElement.dataset.score || 0);
            }
        });
        
        // Calcular puntuación de selección múltiple
        const multipleSelectionInputs = form.querySelectorAll('input[type="checkbox"]:checked');
        multipleSelectionInputs.forEach(input => {
            const option = input.closest('.form-check');
            const scoreElement = option.querySelector('.score');
            if (scoreElement) {
                totalScore += parseFloat(scoreElement.dataset.score || 0);
            }
        });
        
        // Calcular puntuación máxima
        const allScoreElements = form.querySelectorAll('.score');
        allScoreElements.forEach(element => {
            maxScore += parseFloat(element.dataset.score || 0);
        });
        
        return {
            current: totalScore,
            maximum: maxScore,
            percentage: maxScore > 0 ? (totalScore / maxScore) * 100 : 0
        };
    }
    
    /**
     * Actualiza indicador de puntuación en tiempo real
     */
    updateRealTimeScore(form) {
        const score = this.calculateRealTimeScore(form);
        const scoreIndicator = form.querySelector('.real-time-score');
        
        if (scoreIndicator) {
            scoreIndicator.innerHTML = `
                <div class="score-display">
                    <span class="current-score">${score.current}</span>
                    <span class="separator">/</span>
                    <span class="max-score">${score.maximum}</span>
                    <span class="percentage">(${Math.round(score.percentage)}%)</span>
                </div>
            `;
        }
    }
    
    /**
     * Inicializa todas las funcionalidades
     */
    init() {
        this.initializeEventListeners();
        this.initializeDragAndDrop();
        this.initializeStarRating();
        this.initializeMatching();
        
        // Actualizar puntuación en tiempo real
        document.addEventListener('change', (e) => {
            const form = e.target.closest('form');
            if (form) {
                this.updateRealTimeScore(form);
            }
        });
        
        // Validar formulario al enviar
        document.addEventListener('submit', (e) => {
            const validation = this.validateQuestionForm(e.target);
            if (!validation.isValid) {
                e.preventDefault();
                alert('Por favor corrija los siguientes errores:\n' + validation.errors.join('\n'));
            }
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.questionTypeManager = new QuestionTypeManager();
    window.questionTypeManager.init();
});

// Funciones utilitarias globales
window.QuestionUtils = {
    /**
     * Formatea tiempo en formato legible
     */
    formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        if (hours > 0) {
            return `${hours}h ${minutes}m ${secs}s`;
        } else if (minutes > 0) {
            return `${minutes}m ${secs}s`;
        } else {
            return `${secs}s`;
        }
    },
    
    /**
     * Genera ID único
     */
    generateId() {
        return 'id_' + Math.random().toString(36).substr(2, 9);
    },
    
    /**
     * Debounce function para optimizar eventos
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    /**
     * Throttle function para limitar frecuencia de eventos
     */
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};
