/**
 * NextGen Design System - Component Interactions
 * JavaScript for modals, toasts, and other interactive components
 */

// ========================================
// MODAL COMPONENT
// ========================================
class Modal {
  constructor(modalId) {
    this.modal = document.getElementById(modalId);
    this.backdrop = this.modal?.querySelector('.modal-backdrop');
    this.closeButtons = this.modal?.querySelectorAll('[data-modal-close]');
    this.focusableElements = null;
    this.firstFocusable = null;
    this.lastFocusable = null;
    this.previousActiveElement = null;
    
    if (this.modal) {
      this.init();
    }
  }
  
  init() {
    // Close button handlers
    this.closeButtons?.forEach(btn => {
      btn.addEventListener('click', () => this.close());
    });
    
    // Backdrop click to close
    this.backdrop?.addEventListener('click', (e) => {
      if (e.target === this.backdrop) {
        this.close();
      }
    });
    
    // Escape key to close
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.isOpen()) {
        this.close();
      }
    });
  }
  
  open() {
    if (!this.modal) return;
    
    // Store currently focused element
    this.previousActiveElement = document.activeElement;
    
    // Show modal
    this.modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Setup focus trap
    this.setupFocusTrap();
    
    // Focus first element
    setTimeout(() => {
      this.firstFocusable?.focus();
    }, 100);
  }
  
  close() {
    if (!this.modal) return;
    
    // Add closing animation
    this.modal.classList.add('closing');
    
    setTimeout(() => {
      this.modal.style.display = 'none';
      this.modal.classList.remove('closing');
      document.body.style.overflow = '';
      
      // Return focus to trigger element
      this.previousActiveElement?.focus();
    }, 300);
  }
  
  isOpen() {
    return this.modal?.style.display === 'flex';
  }
  
  setupFocusTrap() {
    // Get all focusable elements
    this.focusableElements = this.modal.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    this.firstFocusable = this.focusableElements[0];
    this.lastFocusable = this.focusableElements[this.focusableElements.length - 1];
    
    // Trap focus
    this.modal.addEventListener('keydown', (e) => {
      if (e.key !== 'Tab') return;
      
      if (e.shiftKey) {
        if (document.activeElement === this.firstFocusable) {
          e.preventDefault();
          this.lastFocusable.focus();
        }
      } else {
        if (document.activeElement === this.lastFocusable) {
          e.preventDefault();
          this.firstFocusable.focus();
        }
      }
    });
  }
}

// ========================================
// TOAST COMPONENT
// ========================================
class Toast {
  constructor() {
    this.container = this.getOrCreateContainer();
  }
  
  getOrCreateContainer() {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    return container;
  }
  
  show(options = {}) {
    const {
      type = 'info',
      title = '',
      message = '',
      duration = 4000,
      closable = true
    } = options;
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Icon based on type
    const icons = {
      success: '✓',
      error: '✕',
      warning: '⚠',
      info: 'ℹ'
    };
    
    toast.innerHTML = `
      <div class="toast-icon">${icons[type]}</div>
      <div class="toast-content">
        ${title ? `<div class="toast-title">${title}</div>` : ''}
        ${message ? `<div class="toast-message">${message}</div>` : ''}
      </div>
      ${closable ? '<button class="toast-close" aria-label="Close">×</button>' : ''}
      ${duration > 0 ? '<div class="toast-progress"><div class="toast-progress-bar"></div></div>' : ''}
    `;
    
    // Add to container
    this.container.appendChild(toast);
    
    // Close button handler
    if (closable) {
      const closeBtn = toast.querySelector('.toast-close');
      closeBtn.addEventListener('click', () => this.close(toast));
    }
    
    // Auto dismiss
    if (duration > 0) {
      setTimeout(() => this.close(toast), duration);
    }
    
    return toast;
  }
  
  close(toast) {
    toast.classList.add('closing');
    setTimeout(() => {
      toast.remove();
    }, 300);
  }
  
  success(title, message, duration) {
    return this.show({ type: 'success', title, message, duration });
  }
  
  error(title, message, duration) {
    return this.show({ type: 'error', title, message, duration });
  }
  
  warning(title, message, duration) {
    return this.show({ type: 'warning', title, message, duration });
  }
  
  info(title, message, duration) {
    return this.show({ type: 'info', title, message, duration });
  }
}

// ========================================
// FORM VALIDATION
// ========================================
class FormValidator {
  constructor(formId) {
    this.form = document.getElementById(formId);
    if (this.form) {
      this.init();
    }
  }
  
  init() {
    this.form.addEventListener('submit', (e) => {
      if (!this.validate()) {
        e.preventDefault();
      }
    });
    
    // Real-time validation
    const inputs = this.form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
      input.addEventListener('blur', () => this.validateField(input));
      input.addEventListener('input', () => {
        if (input.classList.contains('error')) {
          this.validateField(input);
        }
      });
    });
  }
  
  validate() {
    let isValid = true;
    const inputs = this.form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
      if (!this.validateField(input)) {
        isValid = false;
      }
    });
    
    return isValid;
  }
  
  validateField(input) {
    const value = input.value.trim();
    const formGroup = input.closest('.form-group');
    let errorMsg = formGroup?.querySelector('.form-error');
    
    // Remove existing error
    input.classList.remove('error', 'success');
    if (errorMsg) errorMsg.remove();
    
    // Check if required
    if (input.hasAttribute('required') && !value) {
      this.showError(input, 'This field is required');
      return false;
    }
    
    // Email validation
    if (input.type === 'email' && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        this.showError(input, 'Please enter a valid email');
        return false;
      }
    }
    
    // Min length
    if (input.hasAttribute('minlength')) {
      const minLength = parseInt(input.getAttribute('minlength'));
      if (value.length < minLength) {
        this.showError(input, `Minimum ${minLength} characters required`);
        return false;
      }
    }
    
    // Show success
    if (value) {
      input.classList.add('success');
    }
    
    return true;
  }
  
  showError(input, message) {
    input.classList.add('error');
    const formGroup = input.closest('.form-group');
    
    if (formGroup) {
      const errorDiv = document.createElement('div');
      errorDiv.className = 'form-error';
      errorDiv.textContent = message;
      formGroup.appendChild(errorDiv);
    }
  }
}

// ========================================
// CHARACTER COUNTER
// ========================================
function initCharacterCounter(textareaId, maxLength) {
  const textarea = document.getElementById(textareaId);
  if (!textarea) return;
  
  const formGroup = textarea.closest('.form-group');
  const counter = document.createElement('div');
  counter.className = 'form-counter';
  formGroup.appendChild(counter);
  
  function updateCounter() {
    const length = textarea.value.length;
    counter.textContent = `${length} / ${maxLength}`;
    
    counter.classList.remove('warning', 'error');
    if (length > maxLength) {
      counter.classList.add('error');
    } else if (length > maxLength * 0.8) {
      counter.classList.add('warning');
    }
  }
  
  textarea.addEventListener('input', updateCounter);
  updateCounter();
}

// ========================================
// FILE UPLOAD WITH PROGRESS
// ========================================
function initFileUpload(inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  
  input.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    const formGroup = input.closest('.form-group');
    let progressDiv = formGroup.querySelector('.file-upload-progress');
    
    if (!progressDiv) {
      progressDiv = document.createElement('div');
      progressDiv.className = 'file-upload-progress';
      progressDiv.innerHTML = `
        <div class="progress-bar">
          <div class="progress-fill" style="width: 0%"></div>
        </div>
        <div class="progress-text">0%</div>
      `;
      formGroup.appendChild(progressDiv);
    }
    
    const progressFill = progressDiv.querySelector('.progress-fill');
    const progressText = progressDiv.querySelector('.progress-text');
    
    // Simulate upload progress
    let progress = 0;
    const interval = setInterval(() => {
      progress += 10;
      progressFill.style.width = `${progress}%`;
      progressText.textContent = `${progress}%`;
      
      if (progress >= 100) {
        clearInterval(interval);
        setTimeout(() => {
          progressDiv.remove();
        }, 1000);
      }
    }, 200);
  });
}

// ========================================
// LAZY LOADING IMAGES
// ========================================
function initLazyLoading() {
  const images = document.querySelectorAll('img[data-src]');
  
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.removeAttribute('data-src');
        observer.unobserve(img);
      }
    });
  });
  
  images.forEach(img => imageObserver.observe(img));
}

// ========================================
// SCROLL ANIMATIONS
// ========================================
function initScrollAnimations() {
  const elements = document.querySelectorAll('[data-animate]');
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('fade-in');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  
  elements.forEach(el => observer.observe(el));
}

// ========================================
// GLOBAL INITIALIZATION
// ========================================
document.addEventListener('DOMContentLoaded', () => {
  // Initialize lazy loading
  if ('IntersectionObserver' in window) {
    initLazyLoading();
    initScrollAnimations();
  }
  
  // Create global toast instance
  window.toast = new Toast();
});

// ========================================
// EXPORT FOR MODULE USAGE
// ========================================
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { Modal, Toast, FormValidator, initCharacterCounter, initFileUpload };
}
