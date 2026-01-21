/**
 * =============================================================================
 * TASK MANAGER - JAVASCRIPT
 * =============================================================================
 * 
 * Client-side enhancements for the Task Manager application.
 * 
 * @author  Afzal Khan
 * @version 1.0.0
 * @since   January 2026
 * =============================================================================
 */

// =============================================================================
// AUTO-HIDE ALERTS
// =============================================================================

document.addEventListener('DOMContentLoaded', function () {
    // Auto-hide alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s, transform 0.5s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Focus on title input when page loads (if form is visible)
    const titleInput = document.getElementById('title');
    if (titleInput && !titleInput.value) {
        // Only focus if not editing
        titleInput.focus();
    }
});

// =============================================================================
// FORM ENHANCEMENTS
// =============================================================================

/**
 * Set today's date as default in date picker
 */
const dueDateInput = document.getElementById('due_date');
if (dueDateInput && !dueDateInput.value) {
    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0];
    dueDateInput.min = today; // Prevent past dates
}

// =============================================================================
// KEYBOARD SHORTCUTS
// =============================================================================

document.addEventListener('keydown', function (e) {
    // Ctrl+N or Cmd+N to focus on new task input
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        const titleInput = document.getElementById('title');
        if (titleInput) {
            titleInput.focus();
            titleInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});

// =============================================================================
// CONFIRM DIALOGS
// =============================================================================

// Enhanced delete confirmation (already handled in onclick, this is backup)
document.querySelectorAll('a[href*="delete"]').forEach(link => {
    link.addEventListener('click', function (e) {
        if (!confirm('Are you sure you want to delete this task?')) {
            e.preventDefault();
        }
    });
});

// =============================================================================
// LOCAL STORAGE - Remember collapsed sidebar state
// =============================================================================

// Future enhancement: Add sidebar collapse functionality
