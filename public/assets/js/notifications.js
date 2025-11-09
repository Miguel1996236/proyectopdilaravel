/**
 * Sistema de Notificaciones en Tiempo Real
 * Maneja la visualización y gestión de notificaciones
 */

class NotificationManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.pollInterval = null;
        this.isPolling = false;
        
        this.init();
    }
    
    init() {
        this.createNotificationDropdown();
        this.loadNotifications();
        this.startPolling();
        this.bindEvents();
    }
    
    createNotificationDropdown() {
        // Crear el dropdown de notificaciones si no existe
        if (!document.getElementById('notification-dropdown')) {
            const navbar = document.querySelector('.navbar-nav');
            if (navbar) {
                const notificationHTML = `
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger" id="notification-badge" style="display: none;">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="notification-dropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Notificaciones</span>
                                <button class="btn btn-sm btn-outline-primary" id="mark-all-read">Marcar todas</button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li id="notifications-list">
                                <div class="text-center p-3">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </li>
                `;
                navbar.insertAdjacentHTML('beforeend', notificationHTML);
            }
        }
    }
    
    async loadNotifications() {
        try {
            const response = await fetch('api/notificaciones.php?action=obtener&limit=10');
            const data = await response.json();
            
            if (data.success) {
                this.notifications = data.notificaciones;
                this.renderNotifications();
                this.updateUnreadCount();
            }
        } catch (error) {
            console.error('Error cargando notificaciones:', error);
        }
    }
    
    async updateUnreadCount() {
        try {
            const response = await fetch('api/notificaciones.php?action=conteo');
            const data = await response.json();
            
            if (data.success) {
                this.unreadCount = data.total;
                this.updateBadge();
            }
        } catch (error) {
            console.error('Error actualizando conteo:', error);
        }
    }
    
    updateBadge() {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    renderNotifications() {
        const container = document.getElementById('notifications-list');
        if (!container) return;
        
        if (this.notifications.length === 0) {
            container.innerHTML = `
                <div class="text-center p-3 text-muted">
                    <i class="fas fa-bell-slash fa-2x mb-2"></i>
                    <p class="mb-0">No hay notificaciones</p>
                </div>
            `;
            return;
        }
        
        const notificationsHTML = this.notifications.map(notification => `
            <li class="notification-item ${!notification.leida ? 'unread' : ''}" data-id="${notification.id}">
                <a href="#" class="dropdown-item notification-link">
                    <div class="d-flex align-items-start">
                        <div class="notification-icon me-2">
                            ${this.getNotificationIcon(notification.tipo)}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 notification-title">${notification.titulo}</h6>
                            <p class="mb-1 notification-message">${notification.mensaje}</p>
                            <small class="text-muted notification-time">
                                ${this.formatTime(notification.fecha_creacion)}
                            </small>
                        </div>
                        ${!notification.leida ? '<div class="unread-dot"></div>' : ''}
                    </div>
                </a>
            </li>
        `).join('');
        
        container.innerHTML = notificationsHTML;
        
        // Agregar eventos a las notificaciones
        this.bindNotificationEvents();
    }
    
    getNotificationIcon(tipo) {
        const icons = {
            'asignacion': '<i class="fas fa-plus-circle text-primary"></i>',
            'completacion': '<i class="fas fa-check-circle text-success"></i>',
            'expiracion': '<i class="fas fa-clock text-warning"></i>',
            'analisis': '<i class="fas fa-brain text-info"></i>',
            'recordatorio': '<i class="fas fa-bell text-warning"></i>',
            'sistema': '<i class="fas fa-cog text-secondary"></i>',
            'error': '<i class="fas fa-exclamation-triangle text-danger"></i>'
        };
        return icons[tipo] || '<i class="fas fa-bell text-primary"></i>';
    }
    
    formatTime(fecha) {
        const now = new Date();
        const notificationDate = new Date(fecha);
        const diffMs = now - notificationDate;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        if (diffMins < 1) return 'Ahora mismo';
        if (diffMins < 60) return `Hace ${diffMins} min`;
        if (diffHours < 24) return `Hace ${diffHours}h`;
        if (diffDays < 7) return `Hace ${diffDays}d`;
        
        return notificationDate.toLocaleDateString();
    }
    
    bindEvents() {
        // Botón para marcar todas como leídas
        document.addEventListener('click', (e) => {
            if (e.target.id === 'mark-all-read') {
                e.preventDefault();
                this.markAllAsRead();
            }
        });
        
        // Cerrar dropdown cuando se hace clic fuera
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('notification-dropdown');
            const toggle = document.getElementById('notificationDropdown');
            
            if (dropdown && !dropdown.contains(e.target) && !toggle.contains(e.target)) {
                const bsDropdown = new bootstrap.Dropdown(toggle);
                bsDropdown.hide();
            }
        });
    }
    
    bindNotificationEvents() {
        // Eventos para las notificaciones individuales
        document.querySelectorAll('.notification-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const notificationItem = e.target.closest('.notification-item');
                const notificationId = notificationItem.dataset.id;
                
                this.markAsRead(notificationId);
                this.handleNotificationAction(notificationItem);
            });
        });
    }
    
    async markAsRead(notificationId) {
        try {
            const response = await fetch('api/notificaciones.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'marcar_leida',
                    notificacion_id: notificationId
                })
            });
            
            const data = await response.json();
            if (data.success) {
                // Marcar visualmente como leída
                const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('unread');
                    notificationItem.querySelector('.unread-dot')?.remove();
                }
                
                this.updateUnreadCount();
            }
        } catch (error) {
            console.error('Error marcando notificación como leída:', error);
        }
    }
    
    async markAllAsRead() {
        try {
            const response = await fetch('api/notificaciones.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'marcar_todas_leidas'
                })
            });
            
            const data = await response.json();
            if (data.success) {
                // Marcar todas visualmente como leídas
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.remove('unread');
                    item.querySelector('.unread-dot')?.remove();
                });
                
                this.updateUnreadCount();
                this.showToast('Todas las notificaciones marcadas como leídas', 'success');
            }
        } catch (error) {
            console.error('Error marcando todas como leídas:', error);
        }
    }
    
    handleNotificationAction(notificationItem) {
        // Aquí se pueden manejar acciones específicas según el tipo de notificación
        const notification = this.notifications.find(n => n.id == notificationItem.dataset.id);
        
        if (notification && notification.datos_extra) {
            const action = notification.datos_extra.action;
            const cuestionarioId = notification.datos_extra.cuestionario_id;
            const estudianteId = notification.datos_extra.estudiante_id;
            
            switch (action) {
                case 'ver_cuestionario':
                    if (cuestionarioId) {
                        window.location.href = `responder_cuestionario.php?id=${cuestionarioId}`;
                    }
                    break;
                case 'ver_resultados':
                    if (cuestionarioId) {
                        window.location.href = `analisis_cuestionario.php?id=${cuestionarioId}`;
                    }
                    break;
                case 'ver_analisis':
                    if (cuestionarioId) {
                        window.location.href = `analisis_cuestionario.php?id=${cuestionarioId}`;
                    }
                    break;
                case 'gestionar_codigos':
                    window.location.href = 'generar_codigos.php';
                    break;
                case 'responder_cuestionario':
                    if (cuestionarioId) {
                        window.location.href = `responder_cuestionario.php?id=${cuestionarioId}`;
                    }
                    break;
            }
        }
    }
    
    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.pollInterval = setInterval(() => {
            this.updateUnreadCount();
        }, 30000); // Actualizar cada 30 segundos
    }
    
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
            this.isPolling = false;
        }
    }
    
    showToast(message, type = 'info') {
        // Crear toast de notificación
        const toastHTML = `
            <div class="toast align-items-center text-white bg-${type}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        // Agregar al contenedor de toasts
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1055';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        // Mostrar el toast
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Remover del DOM después de que se oculte
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
    
    // Método público para crear notificaciones (para uso desde otras partes del sistema)
    async createNotification(destinatarioId, tipo, titulo, mensaje, datosExtra = null) {
        try {
            const response = await fetch('api/notificaciones.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'crear',
                    destinatario_id: destinatarioId,
                    tipo: tipo,
                    titulo: titulo,
                    mensaje: mensaje,
                    datos_extra: datosExtra
                })
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error creando notificación:', error);
            return { success: false, error: error.message };
        }
    }
}

// Inicializar el sistema de notificaciones cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
});

// CSS adicional para las notificaciones
const notificationCSS = `
<style>
.notification-item.unread {
    background-color: #f8f9fa;
    border-left: 3px solid #007bff;
}

.notification-item.unread .notification-title {
    font-weight: bold;
}

.unread-dot {
    width: 8px;
    height: 8px;
    background-color: #007bff;
    border-radius: 50%;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
}

.notification-item {
    position: relative;
}

.notification-item:hover {
    background-color: #e9ecef;
}

.notification-icon {
    font-size: 1.2em;
}

.notification-time {
    font-size: 0.75em;
}

#notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    font-size: 0.7em;
    min-width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dropdown-menu {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.1);
}
</style>
`;

// Agregar CSS al head
document.head.insertAdjacentHTML('beforeend', notificationCSS);
