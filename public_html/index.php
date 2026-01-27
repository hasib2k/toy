<?php
// Fetch all content from database
$content = ['banners' => [], 'product_images' => [], 'product_details' => null, 'reviews' => [], 'video' => null, 'social_links' => [], 'settings' => [], 'feature_cards' => []];
$dbConnected = false;

try {
    @require_once __DIR__ . '/config/database.php';
    $dbConnected = isset($pdo);
    
    if ($dbConnected) {
        // Fetch banners
        $stmt = $pdo->query('SELECT title, subtitle, image_path FROM banners WHERE is_active = 1 ORDER BY display_order ASC');
        $content['banners'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch product images
        $stmt = $pdo->query('SELECT image_path, alt_text FROM product_images WHERE is_active = 1 ORDER BY display_order ASC');
        $content['product_images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch product details
        $stmt = $pdo->query('SELECT * FROM product_details WHERE product_id = 1 LIMIT 1');
        $content['product_details'] = $stmt->fetch();
        
        // Fetch reviews
        $stmt = $pdo->query('SELECT customer_name, customer_image, rating, review_text FROM reviews WHERE is_active = 1 ORDER BY display_order ASC');
        $content['reviews'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch video
        $stmt = $pdo->query('SELECT title, video_path, thumbnail_path FROM videos WHERE is_active = 1 ORDER BY display_order ASC LIMIT 1');
        $content['video'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Fetch social links
        $stmt = $pdo->query('SELECT platform, url, icon_path FROM social_links WHERE is_active = 1 ORDER BY display_order ASC');
        $content['social_links'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch feature cards
        try {
            $stmt = $pdo->query('SELECT id, image_path, title, display_order FROM feature_cards WHERE is_active = 1 ORDER BY display_order ASC');
            $content['feature_cards'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $content['feature_cards'] = [];
        }
        
        // Fetch settings
        $stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
        $settings_raw = $stmt->fetchAll();
        foreach ($settings_raw as $setting) {
            $content['settings'][$setting['setting_key']] = $setting['setting_value'];
        }
    }
} catch (Exception $e) {
    // Database error - show minimal error for debugging
    error_log("Database error in index.php: " . $e->getMessage());
}

// Helper functions
function getSetting($key, $default = '') {
    global $content;
    return $content['settings'][$key] ?? $default;
}

function getProductDetail($key, $default = '') {
    global $content;
    return $content['product_details'][$key] ?? $default;
}
?>
<!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars(getSetting('site_name', '')) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Bengali:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <header class="top-banner">
    <div class="banner-carousel">
      <div class="banner-track">
        <?php foreach ($content['banners'] as $banner): ?>
          <span class="banner-slide"><?= htmlspecialchars($banner['title']) ?><?= !empty($banner['subtitle']) ? ' — ' . htmlspecialchars($banner['subtitle']) : '' ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </header>
    <!-- navbar removed -->

  <main class="container">
    <section class="product">
      <div class="gallery">
        <!-- Hero Image Carousel -->
        <div class="hero-carousel">
          <div class="carousel-container">
            <div class="carousel-track" id="carouselTrack">
              <?php foreach ($content['product_images'] as $index => $img): ?>
              <div class="carousel-slide <?= $index === 0 ? 'active' : '' ?>">
                <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($img['alt_text']) ?>">
              </div>
              <?php endforeach; ?>
            </div>
            
            <!-- Navigation Arrows -->
            <button class="carousel-nav carousel-prev" aria-label="Previous image">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
            </button>
            <button class="carousel-nav carousel-next" aria-label="Next image">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 6 15 12 9 18"></polyline>
              </svg>
            </button>
          </div>
          
          <!-- Dot Indicators -->
          <div class="carousel-dots">
            <?php foreach ($content['product_images'] as $index => $img): ?>
            <button class="carousel-dot <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>" aria-label="Go to slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
          </div>
          
          <!-- Thumbnail Strip -->
          <div class="thumbs">
            <?php foreach ($content['product_images'] as $index => $img): ?>
            <button class="thumb <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
              <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($img['alt_text']) ?>">
            </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="details">
        <h1 class="title"><?= htmlspecialchars(getProductDetail('name', '')) ?></h1>
        <div class="price-row">
          <div>
            <div class="price">৳<?= number_format(getProductDetail('price', 0), 2) ?></div>
            <?php if (getProductDetail('discount_price')): ?>
            <div class="compare-price">৳<?= number_format(getProductDetail('discount_price'), 2) ?></div>
            <?php endif; ?>
            <?php 
            $price = floatval(getProductDetail('price', 0));
            $discount_price = floatval(getProductDetail('discount_price', 0));
            if ($discount_price > $price) {
                $discount_percent = round((($discount_price - $price) / $discount_price) * 100);
                echo '<div class="discount">' . $discount_percent . '% ছাড়</div>';
            }
            ?>
          </div>
          <div class="stock-actions">
            <button id="orderBtnSmall" class="btn-secondary1 order-trigger">অর্ডার করুন</button>
          </div>
        </div>

        
        <p class="short-desc"><?= htmlspecialchars(getProductDetail('description', '')) ?></p>

        <?php 
        $features = getProductDetail('features', '');
        if ($features):
        ?>
        <div class="who-for">
          <h3>বৈশিষ্ট্য ও উপকারিতা:</h3>
          <ul>
            <?php 
              $items = explode("|", str_replace("\n", "|", $features));
              foreach ($items as $item) {
                $item = trim($item);
                if ($item) echo '<li>' . htmlspecialchars($item) . '</li>';
              }
            ?>
          </ul>
        </div>
        <?php endif; ?>

        <!-- Order buttons moved to the dedicated order section below -->

        <div class="accordion">
          <button class="acc-toggle">বিস্তারিত জানতে ক্লিক করুন ☞</button>
          <div class="acc-panel">
            <?php 
            $acc_desc = getProductDetail('accordion_description', '');
            if ($acc_desc) {
              $paragraphs = explode("\n\n", $acc_desc);
              foreach ($paragraphs as $para) {
                $para = trim($para);
                if ($para) echo '<p class="acc-desc">' . htmlspecialchars($para) . '</p>';
              }
            } else {
            ?>
            <p class="acc-desc">এই লার্নিং এন্ড প্লেয়িং টয়টি শিশুদের জন্য বিশেষভাবে ডিজাইন করা হয়েছে।</p>
            <?php } ?>

              <h5><?= htmlspecialchars(getProductDetail('features_heading', 'বৈশিষ্ট্য ও উপকারিতা:')) ?></h5>
            <ul>
            <?php 
            $features = getProductDetail('features_items', '');
            if ($features) {
              $items = explode("\n", $features);
              foreach ($items as $item) {
                $item = trim($item);
                if ($item) echo '<li>' . htmlspecialchars($item) . '</li>';
              }
            } else {
            ?>
              <li>খেলার ছলে বাংলা ও ইংরেজি শেখা</li>
            <?php } ?>
            </ul>
          </div>
        </div>

      </div>
    </section>

    <section class="video-section">
      <h2>পণ্য ভিডিও</h2>
      <?php 
      $video = $content['video'];
      if ($video):
      ?>
        <div class="video-preview">
          <button id="openVideo" class="video-thumb" aria-label="Play product video">
            <img src="<?= htmlspecialchars( ( $video['thumbnail_path'] ?? '' ) ?: 'assets/images/image.png') ?>" alt="video poster">
            <span class="play-icon" aria-hidden="true">▶</span>
          </button>
          <div class="video-caption"><?= htmlspecialchars( ( $video['caption'] ?? '' ) ?: 'ভিডিও রিভিউ — খেলনা ব্যবহার ও অভিজ্ঞতা দেখুন') ?></div>
        </div>

        <!-- Video modal (local video) -->
        <div id="videoModal" class="video-modal" aria-hidden="true">
          <div class="video-modal-content">
            <button id="closeVideo" class="video-close" aria-label="Close video"></button>
            <div class="video-modal-controls">
              <button id="videoUnmute" class="btn-secondary full">Unmute</button>
            </div>
            <video id="productVideo" controls playsinline preload="metadata" poster="<?= htmlspecialchars( ( $video['thumbnail_path'] ?? '' ) ?: 'assets/images/video-poster.svg') ?>">
              <source src="<?= htmlspecialchars($video['video_path'] ?? '') ?>" type="video/mp4">
              আপনার ব্রাউজার ভিডিও প্লে সমর্থন করে না।
            </video>
          </div>
        </div>
      <?php endif; ?>
    </section>

    <!-- Features Grid Section -->
    <?php if (!empty($content['feature_cards'])): ?>
    <section class="features-section">
      <h2>ফিচারসমূহ</h2>
      <div class="features-grid">
        <?php foreach ($content['feature_cards'] as $card): 
            $rawImg = $card['image_path'] ?? '';
            $fsPath = __DIR__ . '/' . ltrim(str_replace('\\', '/', $rawImg), '/');
            $imgUrl = '/assets/images/toy3d.png';
            if (!empty($rawImg) && file_exists($fsPath)) {
                $imgUrl = '/' . ltrim(str_replace('\\', '/', $rawImg), '/');
            }
        ?>
        <div class="feature-card">
          <div class="feature-image">
            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($card['title'] ?: 'Feature') ?>">
          </div>
          <?php if (!empty($card['title'])): ?>
          <div class="feature-caption"><?= htmlspecialchars($card['title']) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- <section class="related">
      <h2>আপনি হয়ত এগুলোও পছন্দ করবেন</h2>
      <div class="related-list">
        <div class="related-item"><img src="assets/images/product-2.svg" alt="related"><p>পণ্য A</p></div>
        <div class="related-item"><img src="assets/images/product-3.svg" alt="related"><p>পণ্য B</p></div>
        <div class="related-item"><img src="assets/images/product-4.svg" alt="related"><p>পণ্য C</p></div>
      </div>
    </section> -->

    <section class="reviews">
      <h2>কাস্টমার রিভিউ — ইমেজ গ্যালারি</h2>
      <div class="reviews-hint">বামে সোয়াইপ করুন এবং ট্যাপ করে দেখুন</div>
      <div class="review-gallery">
        <?php 
        $reviews = !empty($content['reviews']) ? $content['reviews'] : [
          ['customer_image' => 'assets/images/review2.jpg', 'review_text' => 'Review 1', 'customer_name' => 'Customer 1'],
          ['customer_image' => 'assets/images/review3.jpg', 'review_text' => 'Review 2', 'customer_name' => 'Customer 2']
        ];
        foreach ($reviews as $index => $review): 
            $rawImg = $review['customer_image'] ?? '';
            $fsPath = __DIR__ . '/' . ltrim(str_replace('\\', '/', $rawImg), '/');
            $imgUrl = '/assets/images/review2.jpg';
            if (!empty($rawImg) && file_exists($fsPath)) {
                $imgUrl = '/' . ltrim(str_replace('\\', '/', $rawImg), '/');
            }
        ?>
        <button class="review-thumb" data-src="<?= htmlspecialchars($imgUrl) ?>" aria-label="<?= htmlspecialchars($review['customer_name'] ?: 'Review ' . ($index + 1)) ?>">
          <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($review['customer_name'] ?: 'Review ' . ($index + 1)) ?>">
          <div class="caption"><?= htmlspecialchars($review['review_text'] ?: ($review['customer_name'] ?: 'Review ' . ($index + 1))) ?></div>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Lightbox modal for review images -->
      <div id="reviewLightbox" class="lightbox" aria-hidden="true">
        <div class="lightbox-content">
          <button id="closeLightbox" class="lightbox-close" aria-label="Close"></button>
          <button id="lbPrev" class="lightbox-prev" aria-label="Previous image">‹</button>
          <button id="lbNext" class="lightbox-next" aria-label="Next image">›</button>
          <img id="lightboxImg" src="" alt="Expanded review image">
          <div id="lightboxCaption" class="lightbox-caption"></div>
        </div>
      </div>
    </section>

    <section id="order-cta" class="order-cta">
      <h2>অর্ডার করুন</h2>
      <div class="product-order">
        <div class="pname"><?php
            $bn = htmlspecialchars(getProductDetail('bengali_name', ''));
            $en = htmlspecialchars(getProductDetail('name', ''));
            echo $bn;
            if (!empty($en)) echo '  ' . $en;
        ?></div>
        <div class="price-row"><span class="price">৳990.00</span> <span class="compare-price">৳1650.00</span></div>
        <div class="order-card">
          <img class="order-thumb" src="<?= htmlspecialchars('/' . ltrim(getProductDetail('order_image', 'assets/images/toy3d.png'), '/')) ?>" alt="product">


              <!-- Inline order form (duplicate of modal form but scoped for inline use) -->
              <form id="orderFormInline" class="order-inline-form" novalidate>
                <div class="form-row">
                  <label for="name_inline">নাম</label>
                  <input id="name_inline" name="name" type="text" placeholder="আপনার নাম লিখুন" required>
                </div>

                <div class="form-row">
                  <label for="phone_inline">ফোন</label>
                  <input id="phone_inline" name="phone" type="tel" placeholder="01XXXXXXXXX" required>
                </div>

                <div class="form-row">
                  <label for="address_inline">ঠিকানা</label>
                  <textarea id="address_inline" name="address" rows="2" placeholder="পূর্ণ ঠিকানা (রাস্তা, এলাকা, জেলা)" required></textarea>
                </div>

                <div class="form-row two-col">
                  <div>
                    <label for="qty_inline">পরিমাণ</label>
                    <div class="qty-control">
                      <button type="button" class="qty-btn" data-action="decrease" aria-label="পরিমাণ কমান">−</button>
                      <input id="qty_inline" name="quantity" type="number" value="1" min="1" step="1">
                      <button type="button" class="qty-btn" data-action="increase" aria-label="পরিমাণ বাড়ান">＋</button>
                    </div>
                  </div>
                  <div class="area-control">
                    <label for="area_inline">ডেলিভারি এলাকা</label>
                    <div class="area-toggle area-toggle-inline" role="tablist" aria-label="ডেলিভারি এলাকা (inline)">
                      <button type="button" class="area-option" data-value="inside" aria-pressed="true">ঢাকা শহরের ভিতরে</button>
                      <button type="button" class="area-option" data-value="outside" aria-pressed="false">ঢাকা শহরের বাইরে</button>
                    </div>
                    <select id="area_inline" name="area" class="sr-only">
                      <option value="inside">ঢাকা শহরের ভিতরে</option>
                      <option value="outside">ঢাকা শহরের বাইরে</option>
                    </select>
                  </div>
                </div>

                <input type="hidden" name="product_id" value="1">

                <div class="summary">
                  <div>মূল্য: <span id="basePrice_inline">৳990.00</span></div>
                  <div>শিপিং: <span id="shipping_inline">৳60</span></div>
                  <div><strong>মোট: <span id="orderTotal_inline">৳1050.00</span></strong></div>
                </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">অর্ডার করুন</button>
        </div>
        <div class="order-actions-vertical">
                <button id="whatsappCardBtn" class="btn-secondary1">WhatsApp এ অর্ডার করুন 🗨</button>
        </div>
        <div class="inline-phone-order">
        </div>
                <div id="formMessageInline" class="form-message" role="status" aria-live="polite"></div>

                <!-- Cash-on-delivery note placed directly under the inline form -->
                <p class="cod-note"><?= htmlspecialchars(getSetting('cod_note', '🛒 <strong>ক্যাশ অন ডেলিভারি।</strong> পণ্য হাতে পেয়ে টাকা পরিশোধ করুন।')) ?></p>
              </form>
          </div>
        </div>
      </div>
    </section>

  </main>

  <footer class="footer">
    <div class="footer-inner">
      <div class="social">
        <?php 
        $social_links = !empty($content['social_links']) ? $content['social_links'] : [
          ['platform' => 'Facebook', 'url' => 'https://www.facebook.com/share/1BRiT1FXcY/', 'icon_path' => 'assets/images/icons8-facebook.svg'],
          ['platform' => 'YouTube', 'url' => 'https://m.youtube.com/@BabuToysYT', 'icon_path' => 'assets/images/icons8-youtube.svg'],
          ['platform' => 'TikTok', 'url' => 'https://www.tiktok.com/@babutoys.com/', 'icon_path' => 'assets/images/icons8-tiktok.svg']
        ];
        foreach ($social_links as $social): 
        ?>
        <a class="social-icon" href="<?= htmlspecialchars($social['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($social['platform']) ?>">
          <img src="<?= htmlspecialchars($social['icon_path'] ?: 'assets/images/icons8-' . strtolower($social['platform']) . '.svg') ?>" alt="<?= htmlspecialchars($social['platform']) ?>" />
        </a>
        <?php endforeach; ?>
      </div>
      <div class="copyright"><?php
        $footer = getSetting('footer_text', '');
        if (!empty($footer)) {
            echo htmlspecialchars($footer);
        } else {
            echo '© 2026 ' . htmlspecialchars(getSetting('site_name', 'BabuToys')) . ' - All rights reserved.';
        }
      ?></div>
    </div>
  </footer>

  <!-- Order modal -->
  <div id="orderModal" class="modal" aria-hidden="true">
    <div class="modal-content">
      <button id="closeModal" class="close" aria-label="Close order form"></button>
      <h3>অর্ডার ফরম</h3>

      <form id="orderForm" class="order-modal-form" novalidate>
        <div class="form-row">
          <label for="name">নাম</label>
          <input id="name" name="name" type="text" placeholder="আপনার নাম লিখুন" required>
        </div>

        <div class="form-row">
          <label for="phone">ফোন</label>
          <input id="phone" name="phone" type="tel" placeholder="01XXXXXXXXX" required>
        </div>

        <div class="form-row">
          <label for="address">ঠিকানা</label>
          <textarea id="address" name="address" rows="3" placeholder="পূর্ণ ঠিকানা (রাস্তা, এলাকা, জেলা)" required></textarea>
        </div>

        <div class="form-row two-col">
          <div>
            <label for="qty">পরিমাণ</label>
            <div class="qty-control">
              <button type="button" class="qty-btn" data-action="decrease" aria-label="পরিমাণ কমান">−</button>
              <input id="qty" name="quantity" type="number" value="1" min="1" step="1">
              <button type="button" class="qty-btn" data-action="increase" aria-label="পরিমাণ বাড়ান">＋</button>
            </div>
          </div>
          <div class="area-control">
            <label for="area">ডেলিভারি এলাকা</label>
            <div class="area-toggle" role="tablist" aria-label="ডেলিভারি এলাকা">
              <button type="button" class="area-option" data-value="inside" aria-pressed="true">ঢাকা শহরের ভিতরে</button>
              <button type="button" class="area-option" data-value="outside" aria-pressed="false">ঢাকা শহরের বাইরে</button>
            </div>
            <!-- keep a native select for form submission (visually hidden but accessible) -->
            <select id="area" name="area" class="sr-only">
              <option value="inside">ঢাকা শহরের ভিতরে</option>
              <option value="outside">ঢাকা শহরের বাইরে</option>
            </select>
          </div>
        </div>

        <input type="hidden" name="product_id" value="1">

        <div class="summary">
          <div>মূল্য: <span id="basePrice">৳990.00</span></div>
          <div>শিপিং: <span id="shipping">৳60</span></div>
          <div><strong>মোট: <span id="orderTotal">৳1050.00</span></strong></div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">অর্ডার করুন</button>
        </div>
        <div id="formMessage" class="form-message" role="status" aria-live="polite"></div>
      </form>
    </div>
  </div>

  <!-- Floating Contact Buttons -->
  <?php 
  $whatsapp = getSetting('whatsapp_number', '+8801619703227');
  $phone = getSetting('phone_number', '+8801619703227');
  ?>
  <div class="floating-buttons">
    <a href="https://wa.me/<?= str_replace('+', '', $whatsapp) ?>" class="float-btn whatsapp" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
      <svg viewBox="0 0 24 24" fill="currentColor">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
      </svg>
    </a>
    <a href="tel:<?= $phone ?>" class="float-btn phone" aria-label="Phone Call">
      <svg viewBox="0 0 24 24" fill="currentColor">
        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
      </svg>
    </a>
    <a href="https://m.me/yourpage" class="float-btn messenger" target="_blank" rel="noopener noreferrer" aria-label="Messenger">
      <svg viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.477 2 2 6.145 2 11.243c0 2.936 1.444 5.544 3.71 7.258v3.499l3.366-1.847c.9.25 1.852.383 2.834.383h.09c5.523 0 10-4.145 10-9.25S17.523 2 12 2zm1.004 12.466l-2.545-2.716-4.97 2.716 5.467-5.804 2.608 2.716 4.906-2.716-5.466 5.804z"/>
      </svg>
    </a>
  </div>

  <script>
    // Pass dynamic settings to JavaScript
    window.SITE_CONFIG = {
      product_price: <?= getProductDetail('price', 0) ?>,
      shipping_inside: <?= getSetting('shipping_inside_dhaka', 0) ?>,
      shipping_outside: <?= getSetting('shipping_outside_dhaka', 0) ?>,
      whatsapp_number: '<?= getSetting('whatsapp_number', '') ?>'
    };
  </script>
  <script src="assets/js/main.js"></script>
</body>
</html>