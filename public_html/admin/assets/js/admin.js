// Admin Panel JavaScript
// This file contains common admin functionality

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin panel loaded');
});

// Sidebar toggle function
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('active');
}
