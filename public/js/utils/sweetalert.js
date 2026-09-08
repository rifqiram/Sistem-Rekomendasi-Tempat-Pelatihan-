/**
 * SweetAlert Modern Helper
 * Elegant, minimalist, and reusable configuration based on SweetAlert2.
 */

// 1. Base Configuration with CSS Classes
const ModernSwal = Swal.mixin({
    buttonsStyling: false,
    showCancelButton: false,
    showDenyButton: false,
    showClass: {
        popup: 'swal2-show',
        backdrop: 'swal2-backdrop-show'
    },
    hideClass: {
        popup: 'swal2-hide',
        backdrop: 'swal2-backdrop-hide'
    },
    customClass: {
        popup: 'modern-swal-popup',
        title: 'modern-swal-title',
        htmlContainer: 'modern-swal-text',
        confirmButton: 'modern-swal-btn modern-swal-btn-primary',
        cancelButton: 'modern-swal-btn modern-swal-btn-secondary',
        denyButton: 'modern-swal-btn modern-swal-btn-danger',
        icon: 'modern-swal-icon',
        actions: 'swal2-actions'
    }
});

// 2. Toast Configuration
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    backdrop: false, // Force no backdrop
    background: '#ffffff', // Clean background
    allowEscapeKey: true,
    customClass: {
        popup: 'modern-swal-toast',
        title: 'swal2-title',
        icon: 'swal2-icon'
    },
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

/**
 * Show Toast Notification (Success, Error, Info, Warning)
 * @param {string} title - Text to display
 * @param {string} type - 'success', 'error', 'info', 'warning'
 */
window.showToast = (title, type = 'success') => {
    return Toast.fire({
        icon: type,
        title: title
    });
};

/**
 * Show Success Message
 * Use for operations where a Toast is not enough and requires user acknowledgment.
 */
window.showSuccess = (title = 'Berhasil', text = '') => {
    // Override standard success to just use Toast, as requested: "SUCCESS: WAJIB menggunakan Toast."
    let message = title;
    if (text) message = `${title}: ${text}`;
    return Toast.fire({
        icon: 'success',
        title: message
    });
};


/**
 * Show Error Message
 * Elegant error handling.
 */
window.showError = (title = 'Terjadi Kesalahan', text = 'Silakan coba beberapa saat lagi.') => {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: text,
        confirmButtonText: 'Tutup',
        showConfirmButton: true,
        showCancelButton: false,
        showDenyButton: false,
        customClass: {
            popup: 'modern-swal-popup',
            title: 'modern-swal-title',
            htmlContainer: 'modern-swal-text',
            confirmButton: 'modern-swal-btn modern-swal-btn-danger',
            icon: 'modern-swal-icon',
            actions: 'swal2-actions'
        }
    });
};

/**
 * Show Warning Message
 */
window.showWarning = (title, text) => {
    return ModernSwal.fire({
        icon: 'warning',
        title: title,
        text: text,
        confirmButtonText: 'Tutup',
        showCancelButton: false,
        showDenyButton: false
    });
};

/**
 * Show Loading State
 * Blocks interaction until Swal.close() is called.
 */
window.showLoading = (text = 'Menyimpan perubahan...') => {
    return ModernSwal.fire({
        title: text,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        showCancelButton: false,
        showDenyButton: false,
        didOpen: () => {
            Swal.showLoading();
        },
        customClass: {
            popup: 'modern-swal-popup modern-swal-loading',
            title: 'modern-swal-title'
        }
    });
};

/**
 * Show Confirmation Modal
 * General purpose confirmation.
 */
window.showConfirm = (title, text, confirmText = 'Ya, Setujui') => {
    return ModernSwal.fire({
        icon: 'warning', // Use info or question if warning is too severe
        title: title,
        text: text,
        showCancelButton: true,
        showDenyButton: false,
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal',
        reverseButtons: true, // Primary action on the right
        focusCancel: true // Accessibility: prevent accidental enter
    });
};

/**
 * Show Delete Confirmation Modal
 * Specific styling for dangerous actions (Red button).
 */
window.showDelete = (title = 'Hapus Data?', text = 'Data yang sudah dihapus tidak dapat dipulihkan.') => {
    return ModernSwal.fire({
        icon: 'warning',
        title: title,
        text: text,
        showCancelButton: true,
        showDenyButton: false,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            // Override confirm button to be Danger instead of Primary
            confirmButton: 'modern-swal-btn modern-swal-btn-danger',
            cancelButton: 'modern-swal-btn modern-swal-btn-secondary',
            popup: 'modern-swal-popup',
            title: 'modern-swal-title',
            htmlContainer: 'modern-swal-text',
            icon: 'modern-swal-icon',
            actions: 'swal2-actions'
        }
    });
};

/**
 * LEGACY WRAPPER
 * For backwards compatibility while refactoring.
 * Will map old window.showAlert and window.confirmAction to the new ones.
 */
window.showAlert = (type, title, message) => {
    if (type === 'error') return window.showError(title, message);
    if (type === 'success') return window.showSuccess(title, message);
    if (type === 'warning') return window.showWarning(title, message);
    return ModernSwal.fire({ icon: type, title, text: message, confirmButtonText: 'Tutup' });
};

window.confirmAction = (title, text, confirmText, callback) => {
    // If confirmText contains 'Hapus', use showDelete style, else showConfirm
    const isDelete = (confirmText || '').toLowerCase().includes('hapus') ||
                     (title || '').toLowerCase().includes('hapus');

    const modal = isDelete ? window.showDelete(title, text) : window.showConfirm(title, text, confirmText);

    modal.then((result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
    });
};