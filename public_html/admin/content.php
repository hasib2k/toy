<?php
require_once __DIR__ . '/includes/auth_check.php';
@require_once __DIR__ . '/../config/database.php';

// Fetch all content sections
$banners = [];
$product_images = [];
$product_details = null;
$reviews = [];
$videos = [];
$social_links = [];
$site_settings = [];
$feature_cards = [];
$error = null;

try {
    // Check if database is available
    if (!isset($pdo) || $pdo === null) {
        throw new Exception('Database not connected. Please set up MySQL database to use content management.');
    }
    
    // Fetch banners
    $stmt = $pdo->query('SELECT * FROM banners ORDER BY display_order ASC');
    $banners = $stmt->fetchAll();
    
    // Fetch product images
    $stmt = $pdo->query('SELECT * FROM product_images ORDER BY display_order ASC');
    $product_images = $stmt->fetchAll();
    
    // Fetch product details
    $stmt = $pdo->query('SELECT * FROM product_details WHERE product_id = 1 LIMIT 1');
    $product_details = $stmt->fetch();
    
    // Fetch reviews
    $stmt = $pdo->query('SELECT * FROM reviews ORDER BY display_order ASC');
    $reviews = $stmt->fetchAll();
    
    // Fetch videos
    $stmt = $pdo->query('SELECT * FROM videos ORDER BY display_order ASC');
    $videos = $stmt->fetchAll();
    
    // Fetch social links
    $stmt = $pdo->query('SELECT * FROM social_links ORDER BY display_order ASC');
    $social_links = $stmt->fetchAll();
    
    // Fetch feature cards (create table if not exists)
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS feature_cards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image_path VARCHAR(255) NOT NULL,
            title VARCHAR(500),
            display_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $stmt = $pdo->query('SELECT * FROM feature_cards ORDER BY display_order ASC');
        $feature_cards = $stmt->fetchAll();
    } catch (Throwable $e) {
        $feature_cards = [];
    }
    
    // Fetch site settings
    $stmt = $pdo->query('SELECT * FROM site_settings');
    $settings_raw = $stmt->fetchAll();
    foreach ($settings_raw as $setting) {
        $site_settings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Management - Babu Toys Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- JavaScript Functions - Must be in head so they're available for onclick -->
    <script>
        // Tab switching (persists active tab in localStorage)
        function switchTab(tabName) {
            window.currentAdminTab = tabName;
            try { localStorage.setItem('admin_active_tab', tabName); } catch(e) {}
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(btn => btn.classList.remove('active'));

            const tabElement = document.getElementById(tabName + '-tab');
            if (tabElement) tabElement.classList.add('active');

            document.querySelectorAll('.tab').forEach(btn => {
                if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(tabName)) {
                    btn.classList.add('active');
                }
            });
        }

        // On page load, restore previously active tab (if any)
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const saved = localStorage.getItem('admin_active_tab');
                if (saved) {
                    // defer slightly to ensure DOM elements exist
                    setTimeout(() => { if (typeof switchTab === 'function') switchTab(saved); }, 10);
                }
            } catch(e) {}
        });

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('modal-overlay').style.display = 'none';
            hideUploadProgress();
        }

        function showModal() {
            document.getElementById('modal-overlay').style.display = 'block';
            document.getElementById('modal').style.display = 'block';
        }
        
        function showUploadProgress(percent) {
            document.getElementById('upload-progress-container').style.display = 'block';
            document.getElementById('upload-progress-bar').style.width = percent + '%';
            document.getElementById('upload-percent').textContent = percent + '%';
        }
        
        function hideUploadProgress() {
            document.getElementById('upload-progress-container').style.display = 'none';
            document.getElementById('upload-progress-bar').style.width = '0%';
            document.getElementById('upload-percent').textContent = '0%';
        }

        // Toast notifications (right-bottom)
        function showToast(message, type = 'success', timeout = 3000) {
            try {
                // enforce a sensible minimum (2000 ms) so toast is visible briefly
                const minTimeout = 2000;
                const t = Math.max(Number(timeout) || 0, minTimeout);

                let container = document.getElementById('toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toast-container';
                    container.setAttribute('aria-live', 'polite');
                    container.style.position = 'fixed';
                    container.style.right = '18px';
                    container.style.bottom = '18px';
                    container.style.zIndex = '11000';
                    container.style.display = 'flex';
                    container.style.flexDirection = 'column';
                    container.style.gap = '10px';
                    document.body.appendChild(container);
                }

                const toast = document.createElement('div');
                toast.className = 'admin-toast ' + (type === 'error' ? 'admin-toast-error' : 'admin-toast-success');
                toast.style.minWidth = '220px';
                toast.style.maxWidth = '360px';
                toast.style.padding = '12px 16px';
                toast.style.borderRadius = '10px';
                toast.style.boxShadow = '0 6px 18px rgba(0,0,0,0.12)';
                toast.style.color = '#fff';
                toast.style.fontWeight = '600';
                toast.style.fontSize = '13px';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(8px)';
                toast.style.transition = 'all 260ms ease';
                toast.textContent = message;

                if (type === 'error') {
                    toast.style.background = 'linear-gradient(90deg,#ef4444,#c026d3)';
                } else {
                    toast.style.background = 'linear-gradient(90deg,#10b981,#059669)';
                }

                container.appendChild(toast);

                // animate in
                requestAnimationFrame(() => {
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateY(0)';
                });

                // auto remove after enforced time
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(8px)';
                    setTimeout(() => { try { container.removeChild(toast); } catch(e) {} }, 300);
                }, t);
            } catch (e) {
                console.warn('Toast error', e);
            }
        }
        
        // Upload with progress
        async function uploadWithProgress(url, formData) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        showUploadProgress(percent);
                    }
                });
                
                xhr.addEventListener('load', () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            resolve(JSON.parse(xhr.responseText));
                        } catch(e) {
                            reject(new Error('Invalid response'));
                        }
                    } else {
                        reject(new Error('Upload failed'));
                    }
                });
                
                xhr.addEventListener('error', () => reject(new Error('Network error')));
                xhr.open('POST', url);
                xhr.send(formData);
            });
        }

        // Banner functions
        function openBannerModal() {
            document.getElementById('modal-title').textContent = 'Add New Banner';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="">
                <div class="form-group">
                    <label>Banner Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Subtitle (Optional)</label>
                    <input type="text" name="subtitle">
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="0">
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_active" checked> Active</label>
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveBanner;
            showModal();
        }

        function editBanner(banner) {
            document.getElementById('modal-title').textContent = 'Edit Banner';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="${banner.id}">
                <div class="form-group">
                    <label>Banner Title</label>
                    <input type="text" name="title" value="${banner.title}" required>
                </div>
                <div class="form-group">
                    <label>Subtitle (Optional)</label>
                    <input type="text" name="subtitle" value="${banner.subtitle || ''}">
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="${banner.display_order}">
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_active" ${banner.is_active ? 'checked' : ''}> Active</label>
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveBanner;
            showModal();
        }

        async function saveBanner(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            data.is_active = formData.get('is_active') ? 1 : 0;
            
            try {
                const response = await fetch('../api/admin/content.php?type=banner', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                    if (result.success) {
                    showToast('Banner saved!', 'success');
                    try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'banners'); } catch(e) {}
                    location.reload();
                } else {
                    showToast('Error: ' + result.message, 'error');
                }
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
            }
        }

        async function deleteBanner(id) {
            if (!confirm('Delete this banner?')) return;
            try {
                const response = await fetch(`../api/admin/content.php?type=banner&id=${id}`, { method: 'DELETE' });
                const result = await response.json();
                if (result.success) { try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'banners'); } catch(e) {} location.reload(); }
                else showToast('Error: ' + result.message, 'error');
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
            }
        }

        // Product Image functions
        function openProductImageModal() {
            document.getElementById('modal-title').textContent = 'Add Product Image';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="">
                <div class="form-group">
                    <label>Image File</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label>Alt Text</label>
                    <input type="text" name="alt_text" required>
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="0">
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveProductImage;
            showModal();
        }

        function editProductImage(img) {
            document.getElementById('modal-title').textContent = 'Edit Product Image';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="${img.id}">
                <input type="hidden" name="current_image" value="${img.image_path}">
                <div class="form-group">
                    <label>Current Image</label>
                    <img src="${img.image_path ? '/' + img.image_path.replace(/^\/+/, '') : '/assets/images/toy3d.png'}" style="max-width:100px;display:block;margin-bottom:10px;object-fit:cover;">
                    <input type="file" name="image" accept="image/*">
                    <small>Leave empty to keep current image</small>
                </div>
                <div class="form-group">
                    <label>Alt Text</label>
                    <input type="text" name="alt_text" value="${img.alt_text}" required>
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="${img.display_order}">
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveProductImage;
            showModal();
        }

        async function saveProductImage(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = document.getElementById('modal-submit-btn');
            const submitText = document.getElementById('submit-text');
            
            try {
                submitBtn.disabled = true;
                submitText.textContent = 'Uploading...';
                
                const result = await uploadWithProgress('../api/admin/content.php?type=product_image', formData);
                
                if (result.success) {
                    submitText.textContent = '✓ Saved!';
                    setTimeout(() => { try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'product-images'); } catch(e) {} location.reload(); }, 500);
                } else {
                    showToast('Error: ' + result.message, 'error');
                    submitBtn.disabled = false;
                    submitText.textContent = 'Save Changes';
                    hideUploadProgress();
                }
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
                submitBtn.disabled = false;
                submitText.textContent = 'Save Changes';
                hideUploadProgress();
            }
        }

        async function deleteProductImage(id) {
            if (!confirm('Delete this image?')) return;
            try {
                const response = await fetch(`../api/admin/content.php?type=product_image&id=${id}`, { method: 'DELETE' });
                const result = await response.json();
                if (result.success) { try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'product-images'); } catch(e) {} location.reload(); }
                else showToast('Error: ' + result.message, 'error');
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
            }
        }

        // Review functions
        function openReviewModal() {
            document.getElementById('modal-title').textContent = 'Add Review';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="">
                <div class="form-group">
                    <label>Review Image</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <input type="hidden" name="customer_name" value="">
                <input type="hidden" name="review_text" value="">
                <input type="hidden" name="rating" value="5">
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="0">
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveReview;
            showModal();
        }

        function editReview(review) {
            document.getElementById('modal-title').textContent = 'Edit Review';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="${review.id}">
                <div class="form-group">
                    <label>Current Image</label>
                    <img src="${review.customer_image ? '/' + review.customer_image.replace(/^\/+/, '') : '/assets/images/review-1.svg'}" style="width:100px;height:100px;border-radius:8px;display:block;margin-bottom:10px;object-fit:cover;">
                    <label style="margin-top:8px; display:block; font-weight:600; color:#374151;">Replace Image</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <input type="hidden" name="customer_name" value="">
                <input type="hidden" name="review_text" value="">
                <input type="hidden" name="rating" value="5">
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="${review.display_order}">
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveReview;
            showModal();
        }

        async function saveReview(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = document.getElementById('modal-submit-btn');
            const submitText = document.getElementById('submit-text');
            
            try {
                submitBtn.disabled = true;
                submitText.textContent = 'Uploading...';
                
                const result = await uploadWithProgress('../api/admin/content.php?type=review', formData);
                
                if (result.success) {
                    submitText.textContent = '✓ Saved!';
                    setTimeout(() => { try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'reviews'); } catch(e) {} location.reload(); }, 500);
                } else {
                    showToast('Error: ' + result.message, 'error');
                    submitBtn.disabled = false;
                    submitText.textContent = 'Save Changes';
                    hideUploadProgress();
                }
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
                submitBtn.disabled = false;
                submitText.textContent = 'Save Changes';
                hideUploadProgress();
            }
        }

        async function deleteReview(id) {
            if (!confirm('Delete this review?')) return;
            try {
                const response = await fetch(`../api/admin/content.php?type=review&id=${id}`, { method: 'DELETE' });
                const result = await response.json();
                if (result.success) { try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'reviews'); } catch(e) {} location.reload(); }
                else showToast('Error: ' + result.message, 'error');
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
            }
        }

        // Video functions
        function openVideoModal() {
            document.getElementById('modal-title').textContent = 'Add Video';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="">
                <div class="form-group">
                    <label>Video Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Video File</label>
                    <input type="file" name="video" accept="video/*" required>
                </div>
                <div class="form-group">
                    <label>Thumbnail (Optional)</label>
                    <input type="file" name="thumbnail" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="0">
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveVideo;
            showModal();
        }

        function editVideo(video) {
            document.getElementById('modal-title').textContent = 'Edit Video';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="${video.id}">
                <div class="form-group">
                    <label>Video Title</label>
                    <input type="text" name="title" value="${video.title}" required>
                </div>
                <div class="form-group">
                    <label>Video File</label>
                    <input type="file" name="video" accept="video/*">
                    <small>Leave empty to keep current video</small>
                </div>
                <div class="form-group">
                    <label>Thumbnail</label>
                    <input type="file" name="thumbnail" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="${video.display_order}">
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveVideo;
            showModal();
        }

        async function saveVideo(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = document.getElementById('modal-submit-btn');
            const submitText = document.getElementById('submit-text');
            
            try {
                submitBtn.disabled = true;
                submitText.textContent = 'Uploading Video...';
                
                const result = await uploadWithProgress('../api/admin/content.php?type=video', formData);
                
                if (result.success) {
                    submitText.textContent = '✓ Saved!';
                    setTimeout(() => { try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'videos'); } catch(e) {} location.reload(); }, 500);
                } else {
                    showToast('Error: ' + result.message, 'error');
                    submitBtn.disabled = false;
                    submitText.textContent = 'Save Changes';
                    hideUploadProgress();
                }
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
                submitBtn.disabled = false;
                submitText.textContent = 'Save Changes';
                hideUploadProgress();
            }
        }

        async function deleteVideo(id) {
            if (!confirm('Delete this video?')) return;
            try {
                const response = await fetch(`../api/admin/content.php?type=video&id=${id}`, { method: 'DELETE' });
                const result = await response.json();
                if (result.success) { try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'videos'); } catch(e) {} location.reload(); }
                else showToast('Error: ' + result.message, 'error');
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
            }
        }

        // Social Link functions
        function openSocialModal() {
            document.getElementById('modal-title').textContent = 'Add Social Link';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="">
                <div class="form-group">
                    <label>Platform</label>
                    <select name="platform" required>
                        <option value="facebook">Facebook</option>
                        <option value="youtube">YouTube</option>
                        <option value="tiktok">TikTok</option>
                        <option value="instagram">Instagram</option>
                        <option value="twitter">Twitter</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>URL</label>
                    <input type="url" name="url" required>
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="0">
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveSocial;
            showModal();
        }

        function editSocial(social) {
            document.getElementById('modal-title').textContent = 'Edit Social Link';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="${social.id}">
                <div class="form-group">
                    <label>Platform</label>
                    <select name="platform" required>
                        <option value="facebook" ${social.platform === 'facebook' ? 'selected' : ''}>Facebook</option>
                        <option value="youtube" ${social.platform === 'youtube' ? 'selected' : ''}>YouTube</option>
                        <option value="tiktok" ${social.platform === 'tiktok' ? 'selected' : ''}>TikTok</option>
                        <option value="instagram" ${social.platform === 'instagram' ? 'selected' : ''}>Instagram</option>
                        <option value="twitter" ${social.platform === 'twitter' ? 'selected' : ''}>Twitter</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>URL</label>
                    <input type="url" name="url" value="${social.url}" required>
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="${social.display_order}">
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveSocial;
            showModal();
        }

        async function saveSocial(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            try {
                const response = await fetch('../api/admin/content.php?type=social', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.success) {
                    showToast('Social link saved!', 'success');
                    try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'social'); } catch(e) {}
                    location.reload();
                } else {
                    showToast('Error: ' + result.message, 'error');
                }
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
            }
        }

        async function deleteSocial(id) {
            if (!confirm('Delete this social link?')) return;
            try {
                const response = await fetch(`../api/admin/content.php?type=social&id=${id}`, { method: 'DELETE' });
                const result = await response.json();
                if (result.success) { try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'social'); } catch(e) {} location.reload(); }
                else showToast('Error: ' + result.message, 'error');
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
            }
        }

        // Feature Card functions
        function openFeatureCardModal() {
            document.getElementById('modal-title').textContent = 'Add Feature Card';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="">
                <div class="form-group">
                    <label>Feature Image</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label>Title (Caption below image)</label>
                    <textarea name="title" rows="2" placeholder="যেমন: খেলার ছলে পড়া শিখবে মনোযোগ বাড়বে"></textarea>
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="0">
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveFeatureCard;
            showModal();
        }

        function editFeatureCard(card) {
            document.getElementById('modal-title').textContent = 'Edit Feature Card';
            document.getElementById('modal-body').innerHTML = `
                <input type="hidden" name="id" value="${card.id}">
                <input type="hidden" name="current_image" value="${card.image_path}">
                <div class="form-group">
                    <label>Current Image</label>
                    <img src="${card.image_path ? '/' + card.image_path.replace(/^\/+/, '') : '/assets/images/toy3d.png'}" style="max-width:150px;display:block;margin-bottom:10px;border-radius:8px;object-fit:cover;">
                    <input type="file" name="image" accept="image/*">
                    <small>Leave empty to keep current image</small>
                </div>
                <div class="form-group">
                    <label>Title (Caption below image)</label>
                    <textarea name="title" rows="2">${card.title || ''}</textarea>
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="${card.display_order}">
                </div>
            `;
            document.getElementById('modal-form').onsubmit = saveFeatureCard;
            showModal();
        }

        async function saveFeatureCard(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = document.getElementById('modal-submit-btn');
            const submitText = document.getElementById('submit-text');
            
            try {
                submitBtn.disabled = true;
                submitText.textContent = 'Uploading...';
                
                const result = await uploadWithProgress('../api/admin/content.php?type=feature_card', formData);
                
                if (result.success) {
                    submitText.textContent = '✓ Saved!';
                    setTimeout(() => { try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'features'); } catch(e) {} location.reload(); }, 500);
                } else {
                    showToast('Error: ' + result.message, 'error');
                    submitBtn.disabled = false;
                    submitText.textContent = 'Save Changes';
                    hideUploadProgress();
                }
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
                submitBtn.disabled = false;
                submitText.textContent = 'Save Changes';
                hideUploadProgress();
            }
        }

        async function deleteFeatureCard(id) {
            if (!confirm('Delete this feature card?')) return;
            try {
                const response = await fetch(`../api/admin/content.php?type=feature_card&id=${id}`, { method: 'DELETE' });
                const result = await response.json();
                if (result.success) { try { localStorage.setItem('admin_active_tab', window.currentAdminTab || 'features'); } catch(e) {} location.reload(); }
                else showToast('Error: ' + result.message, 'error');
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
            }
        }
    </script>
    
    <style>
        /* Content Management Page - Clean & Functional Design */
        .page-content {
            background: #f8fafc;
            padding: 24px;
            min-height: calc(100vh - 64px);
        }
        
        .page-header {
            margin-bottom: 24px;
        }
        
        .page-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .page-header p {
            color: #64748b;
            font-size: 14px;
            margin-top: 4px;
        }
        
        .db-error {
            background: #fef2f2;
            color: #dc2626;
            padding: 20px;
            margin-bottom: 24px;
            border-radius: 12px;
            border-left: 4px solid #dc2626;
        }
        
        .db-error h2 {
            margin-bottom: 12px;
            font-size: 18px;
        }
        
        .db-error code {
            background: rgba(0,0,0,0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        /* Tab Navigation */
        .tabs {
            display: flex;
            background: white;
            padding: 6px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            gap: 4px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .tabs::-webkit-scrollbar {
            height: 0;
        }
        
        .tab {
            padding: 10px 16px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            border-radius: 8px;
            transition: all 0.2s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .tab:hover {
            background: #f1f5f9;
            color: #334155;
        }
        
        .tab.active {
            background: #3b82f6;
            color: white;
        }
        
        .tab svg {
            width: 16px;
            height: 16px;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.2s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Item List */
        .item-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        
        .item-row:hover {
            border-color: #3b82f6;
            box-shadow: 0 2px 8px rgba(59,130,246,0.1);
        }
        
        .item-info {
            flex: 1;
            min-width: 0;
        }
        
        .item-info strong {
            display: block;
            color: #1e293b;
            font-size: 14px;
            margin-bottom: 4px;
            word-break: break-word;
        }
        
        .item-info small {
            color: #64748b;
            font-size: 12px;
        }
        
        .item-info img {
            max-width: 60px;
            max-height: 60px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 12px;
        }
        
        .item-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }
        
        /* Buttons */
        .btn {
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
        }
        
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            backdrop-filter: blur(4px);
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            width: 100%;
            max-width: 480px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            animation: modalSlideIn 0.3s ease;
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
        
        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }
        
        .modal-header h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: #1e293b;
        }
        
        .close-modal {
            background: #f1f5f9;
            border: none;
            font-size: 20px;
            color: #64748b;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .close-modal:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        
        #modal-body {
            padding: 24px;
        }
        
        #modal-body .form-group {
            margin-bottom: 16px;
        }
        
        #modal-body img {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        #modal-form button[type="submit"] {
            width: 100%;
            margin-top: 8px;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }
        
        .empty-state svg {
            width: 48px;
            height: 48px;
            color: #cbd5e1;
            margin-bottom: 12px;
        }
        
        /* Status Badge */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .badge-success {
            background: #dcfce7;
            color: #166534;
        }
        
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        /* Toast styles (global) */
        .admin-toast-success { }
        .admin-toast-error { }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" onclick="toggleSidebar()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <h1 class="page-title">Content Management</h1>
                </div>
                
                <div class="topbar-right">
                    <div class="topbar-user">
                        <div class="user-avatar"><?= strtoupper(substr($_SESSION['admin_user'] ?? 'A', 0, 1)) ?></div>
                        <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                    <a href="logout.php" class="btn-logout">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </a>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="page-content">

            <?php if ($error): ?>
            <div class="db-error">
                <h2>⚠️ Database Not Connected</h2>
                <p><strong>Error:</strong> <?= htmlspecialchars($error) ?></p>
                <p>Please set up MySQL database first.</p>
            </div>
            <?php else: ?>

            <!-- Tab Navigation -->
            <div class="tabs">
                <button type="button" class="tab active" onclick="switchTab('banners')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/></svg>
                    Banners
                </button>
                <button type="button" class="tab" onclick="switchTab('product-images')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                    Images
                </button>
                <button type="button" class="tab" onclick="switchTab('product-details')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                    Details
                </button>
                <button type="button" class="tab" onclick="switchTab('reviews')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Reviews
                </button>
                <button type="button" class="tab" onclick="switchTab('videos')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                    Videos
                </button>
                <button type="button" class="tab" onclick="switchTab('social')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    Social
                </button>
                <button type="button" class="tab" onclick="switchTab('features')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Features
                </button>
                <button type="button" class="tab" onclick="switchTab('settings')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    Settings
                </button>
            </div>
            <!-- Banners Tab -->
            <div id="banners-tab" class="tab-content active">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Banner Management</h3>
                        <button type="button" class="btn btn-success btn-sm" onclick="openBannerModal()">+ Add Banner</button>
                    </div>
                    <div class="card-body">
                        <div class="item-list" id="banners-list">
                            <?php if (empty($banners)): ?>
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/></svg>
                                <p>No banners yet</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($banners as $banner): ?>
                            <div class="item-row" data-id="<?= $banner['id'] ?>">
                                <div class="item-info">
                                    <strong><?= htmlspecialchars($banner['title']) ?></strong>
                                    <small>
                                        <?= !empty($banner['subtitle']) ? htmlspecialchars($banner['subtitle']) . ' • ' : '' ?>
                                        Order: <?= $banner['display_order'] ?> • 
                                        <span class="badge <?= $banner['is_active'] ? 'badge-success' : 'badge-warning' ?>"><?= $banner['is_active'] ? 'Active' : 'Inactive' ?></span>
                                    </small>
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="btn btn-primary btn-sm" onclick='editBanner(<?= json_encode($banner) ?>)'>Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteBanner(<?= $banner['id'] ?>)">Delete</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Images Tab -->
            <div id="product-images-tab" class="tab-content">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Product Images</h3>
                        <button type="button" class="btn btn-success btn-sm" onclick="openProductImageModal()">+ Add Image</button>
                    </div>
                    <div class="card-body">
                        <div class="item-list" id="product-images-list">
                            <?php if (empty($product_images)): ?>
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                <p>No images yet</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($product_images as $img): 
                                $rawImg = $img['image_path'] ?? '';
                                $fsPath = __DIR__ . '/../' . ltrim(str_replace('\\', '/', $rawImg), '/');
                                $imgUrl = '/assets/images/toy3d.png';
                                if (!empty($rawImg) && file_exists($fsPath)) {
                                    $imgUrl = '/' . ltrim(str_replace('\\', '/', $rawImg), '/');
                                }
                                $jsonImg = $img;
                                $jsonImg['image_path'] = ltrim(str_replace('\\', '/', $rawImg), '/');
                            ?>
                            <div class="item-row" data-id="<?= $img['id'] ?>">
                                <div class="item-info" style="display:flex; gap:12px; align-items:center;">
                                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Product Image" style="width:80px; height:80px; object-fit:cover; border-radius:8px;">
                                    <div>
                                        <strong><?= htmlspecialchars($img['alt_text']) ?></strong>
                                        <small>Order: <?= $img['display_order'] ?></small>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="btn btn-primary btn-sm" onclick='editProductImage(<?= json_encode($jsonImg) ?>)'>Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteProductImage(<?= $img['id'] ?>)">Delete</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details Tab -->
            <div id="product-details-tab" class="tab-content">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Product Information</h3>
                    </div>
                    <div class="card-body">
                        <form id="product-details-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Product Name</label>
                                    <input type="text" name="name" value="<?= htmlspecialchars($product_details['name'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Bengali Name (বাংলা নাম)</label>
                                    <input type="text" name="bengali_name" value="<?= htmlspecialchars($product_details['bengali_name'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" rows="3"><?= htmlspecialchars($product_details['description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Price (৳)</label>
                                    <input type="number" step="0.01" name="price" value="<?= $product_details['price'] ?? '990.00' ?>" required>
                                </div>
                                <div class="form-group">
                                <label>Discount Price (৳)</label>
                                <input type="number" step="0.01" name="discount_price" value="<?= $product_details['discount_price'] ?? '' ?>">
                            </div>
                            </div>
                            <div class="form-group">
                                <label>Features (one per line or separated by |)</label>
                                <textarea name="features" rows="4"><?= htmlspecialchars($product_details['features'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Order Form Image</label>
                                <div style="display:flex; gap:12px; align-items:center;">
                                    <img id="order-image-preview" src="<?= htmlspecialchars('/' . ltrim($product_details['order_image'] ?? 'assets/images/toy3d.png', '/')) ?>" alt="Order image" style="width:160px; height:160px; object-fit:cover; border-radius:12px; border:1px solid #e6eef6; background:white; padding:8px;">
                                    <div style="flex:1;">
                                        <input type="hidden" name="order_image" id="order_image" value="<?= htmlspecialchars($product_details['order_image'] ?? '') ?>">
                                        <input type="file" id="order_image_file" accept="image/*">
                                        <small style="display:block; margin-top:8px; color:#64748b;">Upload an image to show on the order form (left thumbnail).</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h4 style="margin:8px 0 12px;">Accordion / Card Section (Edit content shown under "বিস্তারিত জানতে ক্লিক করুন ☞")</h4>
                            <div class="form-group">
                                <label>Accordion Title</label>
                                <input type="text" name="accordion_title" value="<?= htmlspecialchars($product_details['accordion_title'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Accordion Description (Paragraphs - separate by blank line)</label>
                                <textarea name="accordion_description" rows="4"><?= htmlspecialchars($product_details['accordion_description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Features Heading (inside accordion)</label>
                                <input type="text" name="features_heading" value="<?= htmlspecialchars($product_details['features_heading'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Features Items (one per line)</label>
                                <textarea name="features_items" rows="4"><?= htmlspecialchars($product_details['features_items'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Save Product Details</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div id="reviews-tab" class="tab-content">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Customer Reviews</h3>
                        <button type="button" class="btn btn-success btn-sm" onclick="openReviewModal()">+ Add Review</button>
                    </div>
                    <div class="card-body">
                        <div class="item-list" id="reviews-list">
                            <?php if (empty($reviews)): ?>
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <p>No reviews yet</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($reviews as $review): 
                                $rawImg = $review['customer_image'] ?? '';
                                $fsPath = __DIR__ . '/../' . ltrim(str_replace('\\', '/', $rawImg), '/');
                                $imgUrl = '/assets/images/review-1.svg';
                                if (!empty($rawImg) && file_exists($fsPath)) {
                                    $imgUrl = '/' . ltrim(str_replace('\\', '/', $rawImg), '/');
                                }
                                // normalize for JSON passed to JS
                                $jsonReview = $review;
                                $jsonReview['customer_image'] = ltrim(str_replace('\\', '/', $rawImg), '/');
                            ?>
                            <div class="item-row" data-id="<?= $review['id'] ?>">
                                <div class="item-info" style="display:flex; gap:12px; align-items:center;">
                                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Review" style="width:100px; height:100px; border-radius:8px; object-fit:cover;">
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="btn btn-primary btn-sm" onclick='editReview(<?= json_encode($jsonReview) ?>)'>Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteReview(<?= $review['id'] ?>)">Delete</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Videos Tab -->
            <div id="videos-tab" class="tab-content">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Product Videos</h3>
                        <button type="button" class="btn btn-success btn-sm" onclick="openVideoModal()">+ Add Video</button>
                    </div>
                    <div class="card-body">
                        <div class="item-list" id="videos-list">
                            <?php if (empty($videos)): ?>
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                                <p>No videos yet</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($videos as $video): ?>
                            <div class="item-row" data-id="<?= $video['id'] ?>">
                                <div class="item-info" style="display:flex; gap:12px; align-items:center;">
                                    <?php if ($video['thumbnail_path']): ?>
                                    <img src="<?= htmlspecialchars($video['thumbnail_path']) ?>" alt="Video">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= htmlspecialchars($video['title']) ?></strong>
                                        <small><?= htmlspecialchars(basename($video['video_path'])) ?></small>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="btn btn-primary btn-sm" onclick='editVideo(<?= json_encode($video) ?>)'>Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteVideo(<?= $video['id'] ?>)">Delete</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Links Tab -->
            <div id="social-tab" class="tab-content">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Social Media Links</h3>
                        <button type="button" class="btn btn-success btn-sm" onclick="openSocialModal()">+ Add Link</button>
                    </div>
                    <div class="card-body">
                        <div class="item-list" id="social-list">
                            <?php if (empty($social_links)): ?>
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/></svg>
                                <p>No social links yet</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($social_links as $social): ?>
                            <div class="item-row" data-id="<?= $social['id'] ?>">
                                <div class="item-info">
                                    <strong><?= htmlspecialchars($social['platform']) ?></strong>
                                    <small><?= htmlspecialchars($social['url']) ?></small>
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="btn btn-primary btn-sm" onclick='editSocial(<?= json_encode($social) ?>)'>Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteSocial(<?= $social['id'] ?>)">Delete</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature Cards Tab -->
            <div id="features-tab" class="tab-content">
                <div class="content-card">
                    <div class="card-header">
                        <h3>ফিচারসমূহ (Feature Cards)</h3>
                        <button type="button" class="btn btn-success btn-sm" onclick="openFeatureCardModal()">+ Add Feature</button>
                    </div>
                    <div class="card-body">
                        <div class="item-list" id="feature-cards-list">
                            <?php if (empty($feature_cards)): ?>
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                <p>No feature cards yet</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($feature_cards as $card): 
                                $rawImg = $card['image_path'] ?? '';
                                $fsPath = __DIR__ . '/../' . ltrim(str_replace('\\', '/', $rawImg), '/');
                                $imgUrl = '/assets/images/toy3d.png';
                                if (!empty($rawImg) && file_exists($fsPath)) {
                                    $imgUrl = '/' . ltrim(str_replace('\\', '/', $rawImg), '/');
                                }
                                $jsonCard = $card;
                                $jsonCard['image_path'] = ltrim(str_replace('\\', '/', $rawImg), '/');
                            ?>
                            <div class="item-row" data-id="<?= $card['id'] ?>">
                                <div class="item-info" style="display:flex; gap:12px; align-items:center;">
                                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Feature" style="width:80px; height:80px; object-fit:cover; border-radius:8px;">
                                    <div>
                                        <strong><?= htmlspecialchars($card['title'] ?: 'Feature ' . $card['id']) ?></strong>
                                        <small>Order: <?= $card['display_order'] ?></small>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="btn btn-primary btn-sm" onclick='editFeatureCard(<?= json_encode($jsonCard) ?>)'>Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteFeatureCard(<?= $card['id'] ?>)">Delete</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div id="settings-tab" class="tab-content">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Site Settings</h3>
                    </div>
                    <div class="card-body">
                        <form id="settings-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Site Name</label>
                                    <input type="text" name="site_name" value="<?= htmlspecialchars($site_settings['site_name'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>WhatsApp Number</label>
                                    <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($site_settings['whatsapp_number'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="text" name="phone_number" value="<?= htmlspecialchars($site_settings['phone_number'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Shipping Inside Dhaka (৳)</label>
                                    <input type="number" name="shipping_inside_dhaka" value="<?= htmlspecialchars($site_settings['shipping_inside_dhaka'] ?? '60') ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Shipping Outside Dhaka (৳)</label>
                                    <input type="number" name="shipping_outside_dhaka" value="<?= htmlspecialchars($site_settings['shipping_outside_dhaka'] ?? '120') ?>">
                                </div>
                                <div class="form-group">
                                    <label>COD Note</label>
                                    <input type="text" name="cod_note" value="<?= htmlspecialchars($site_settings['cod_note'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group" style="flex:1 1 100%;">
                                    <label>Footer Text (copyright)</label>
                                    <input type="text" name="footer_text" value="<?= htmlspecialchars($site_settings['footer_text'] ?? '') ?>" placeholder="© 2026 <?= htmlspecialchars($site_settings['site_name'] ?? 'BabuToys') ?> - All rights reserved.">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modern Modal with Upload Progress -->
    <div id="modal-overlay" onclick="closeModal()" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; background:rgba(15,23,42,0.7); z-index:999999; backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);"></div>
    <div id="modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:9999999; width:90%; max-width:500px;">
        <div class="modal-content" style="background:linear-gradient(145deg,#ffffff 0%,#f8fafc 100%); border-radius:24px; box-shadow:0 25px 80px rgba(0,0,0,0.35), 0 10px 30px rgba(0,0,0,0.2); overflow:hidden; animation:modalPop 0.3s cubic-bezier(0.34,1.56,0.64,1);">
            <!-- Modal Header -->
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:24px 28px; position:relative;">
                <h3 id="modal-title" style="font-size:20px; font-weight:700; margin:0; color:white; text-shadow:0 2px 4px rgba(0,0,0,0.1);">Modal Title</h3>
                <button type="button" onclick="closeModal()" style="position:absolute; top:16px; right:16px; background:rgba(255,255,255,0.2); border:none; width:36px; height:36px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                    <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="white" stroke-width="2.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            
            <!-- Upload Progress Bar -->
            <div id="upload-progress-container" style="display:none; padding:0 28px; padding-top:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:13px; font-weight:600; color:#475569;">Uploading...</span>
                    <span id="upload-percent" style="font-size:13px; font-weight:700; color:#667eea;">0%</span>
                </div>
                <div style="background:#e2e8f0; border-radius:10px; height:8px; overflow:hidden;">
                    <div id="upload-progress-bar" style="width:0%; height:100%; background:linear-gradient(90deg,#667eea,#764ba2); border-radius:10px; transition:width 0.3s ease;"></div>
                </div>
            </div>
            
            <!-- Modal Form -->
            <form id="modal-form">
                <div id="modal-body" style="padding:28px; max-height:60vh; overflow-y:auto;"></div>
                <div style="padding:0 28px 28px; display:flex; gap:12px;">
                    <button type="button" onclick="closeModal()" style="flex:1; padding:14px 20px; font-size:14px; font-weight:600; background:#f1f5f9; color:#475569; border:none; border-radius:12px; cursor:pointer; transition:all 0.2s;">Cancel</button>
                    <button type="submit" id="modal-submit-btn" style="flex:2; padding:14px 20px; font-size:14px; font-weight:600; background:linear-gradient(135deg,#10b981 0%,#059669 100%); color:white; border:none; border-radius:12px; cursor:pointer; transition:all 0.2s; box-shadow:0 4px 15px rgba(16,185,129,0.3);">
                        <span id="submit-text">Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <style>
        @keyframes modalPop {
            0% { opacity:0; transform:scale(0.9); }
            100% { opacity:1; transform:scale(1); }
        }
        #modal .modal-content button:hover { filter:brightness(1.05); transform:translateY(-1px); }
        #modal-body .form-group { margin-bottom:20px; }
        #modal-body .form-group label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:8px; }
        #modal-body .form-group input[type="text"],
        #modal-body .form-group input[type="number"],
        #modal-body .form-group input[type="url"],
        #modal-body .form-group textarea,
        #modal-body .form-group select {
            width:100%; padding:12px 16px; font-size:14px; border:2px solid #e2e8f0; border-radius:10px;
            transition:all 0.2s; background:#f8fafc; outline:none;
        }
        #modal-body .form-group input:focus,
        #modal-body .form-group textarea:focus,
        #modal-body .form-group select:focus {
            border-color:#667eea; background:white; box-shadow:0 0 0 4px rgba(102,126,234,0.1);
        }
        #modal-body .form-group input[type="file"] {
            padding:12px; background:#f1f5f9; border:2px dashed #cbd5e1; border-radius:10px; cursor:pointer;
        }
        #modal-body .form-group input[type="file"]:hover { border-color:#667eea; background:#f8fafc; }
        #modal-body .form-group input[type="checkbox"] { width:18px; height:18px; accent-color:#667eea; }
        #modal-body .form-group small { display:block; margin-top:6px; font-size:12px; color:#94a3b8; }
    </style>

    <script src="assets/js/admin.js"></script>
    <script>
        // Initialize form handlers on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            // Product Details form
            const productDetailsForm = document.getElementById('product-details-form');
            if (productDetailsForm) {
                productDetailsForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(e.target);
                    const data = Object.fromEntries(formData);
                    
                    try {
                        const response = await fetch('../api/admin/content.php?type=product_details', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify(data)
                        });
                        const result = await response.json();
                        if (result.success) {
                            showToast('Product details saved!', 'success');
                        } else {
                            showToast('Error: ' + result.message, 'error');
                        }
                    } catch (error) {
                        showToast('Error: ' + error.message, 'error');
                    }
                });
                // Order image upload handler
                const orderFile = document.getElementById('order_image_file');
                const orderPreview = document.getElementById('order-image-preview');
                const orderHidden = document.getElementById('order_image');
                if (orderFile) {
                    orderFile.addEventListener('change', async (e) => {
                        const file = e.target.files[0];
                        if (!file) return;
                        const fd = new FormData();
                        fd.append('order_image', file);
                        try {
                            const res = await uploadWithProgress('../api/admin/content.php?type=product_details_image', fd);
                            if (res.success) {
                                orderHidden.value = res.path;
                                orderPreview.src = '/' + res.path.replace(/^\/+/, '');
                                showToast('Order image uploaded', 'success');
                            } else {
                                showToast('Upload failed: ' + (res.message || ''), 'error');
                            }
                        } catch (err) {
                            showToast('Upload error: ' + err.message, 'error');
                        }
                    });
                }
            }

            // Settings form
            const settingsForm = document.getElementById('settings-form');
            if (settingsForm) {
                settingsForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(e.target);
                    const data = Object.fromEntries(formData);
                    
                    try {
                        const response = await fetch('../api/admin/content.php?type=settings', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify(data)
                        });
                        const result = await response.json();
                        if (result.success) {
                            showToast('Settings saved!', 'success');
                        } else {
                            showToast('Error: ' + result.message, 'error');
                        }
                    } catch (error) {
                        showToast('Error: ' + error.message, 'error');
                    }
                });
            }
        });
    </script>
</body>
</html>