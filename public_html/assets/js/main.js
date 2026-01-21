// Basic interactions for the single-product page
document.addEventListener('DOMContentLoaded', function(){
  // ========== Hero Image Carousel ==========
  var carouselTrack = document.getElementById('carouselTrack');
  var carouselSlides = document.querySelectorAll('.carousel-slide');
  var carouselPrev = document.querySelector('.carousel-prev');
  var carouselNext = document.querySelector('.carousel-next');
  var carouselDots = document.querySelectorAll('.carousel-dot');
  var thumbButtons = document.querySelectorAll('.thumbs .thumb');
  var currentSlide = 0;
  var totalSlides = carouselSlides.length;
  var autoplayInterval = null;
  var touchStartX = 0;
  var touchEndX = 0;

  function updateCarousel(index, smooth) {
    if (index < 0) index = totalSlides - 1;
    if (index >= totalSlides) index = 0;
    currentSlide = index;
    
    if (carouselTrack) {
      carouselTrack.style.transition = smooth !== false ? 'transform 0.4s cubic-bezier(0.22, 1, 0.36, 1)' : 'none';
      carouselTrack.style.transform = 'translateX(-' + (currentSlide * 100) + '%)';
    }
    
    // Update dots
    carouselDots.forEach(function(dot, i) {
      dot.classList.toggle('active', i === currentSlide);
    });
    
    // Update thumbnails
    thumbButtons.forEach(function(thumb, i) {
      thumb.classList.toggle('active', i === currentSlide);
    });
  }

  // Navigation arrows
  if (carouselPrev) {
    carouselPrev.addEventListener('click', function() {
      updateCarousel(currentSlide - 1);
      resetAutoplay();
    });
  }
  
  if (carouselNext) {
    carouselNext.addEventListener('click', function() {
      updateCarousel(currentSlide + 1);
      resetAutoplay();
    });
  }

  // Dot navigation
  carouselDots.forEach(function(dot, index) {
    dot.addEventListener('click', function() {
      updateCarousel(index);
      resetAutoplay();
    });
  });

  // Thumbnail navigation
  thumbButtons.forEach(function(thumb, index) {
    thumb.addEventListener('click', function() {
      updateCarousel(index);
      resetAutoplay();
    });
  });

  // Touch/swipe support for mobile
  if (carouselTrack) {
    carouselTrack.addEventListener('touchstart', function(e) {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    carouselTrack.addEventListener('touchend', function(e) {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    }, { passive: true });
  }

  function handleSwipe() {
    var swipeThreshold = 50;
    var diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > swipeThreshold) {
      if (diff > 0) {
        // Swipe left - next slide
        updateCarousel(currentSlide + 1);
      } else {
        // Swipe right - previous slide
        updateCarousel(currentSlide - 1);
      }
      resetAutoplay();
    }
  }

  // Autoplay (optional - 5 second interval)
  function startAutoplay() {
    autoplayInterval = setInterval(function() {
      updateCarousel(currentSlide + 1);
    }, 5000);
  }

  function resetAutoplay() {
    if (autoplayInterval) {
      clearInterval(autoplayInterval);
    }
    startAutoplay();
  }

  // Start autoplay on load
  if (carouselSlides.length > 1) {
    startAutoplay();
  }

  // Pause autoplay on hover (desktop)
  var carouselContainer = document.querySelector('.carousel-container');
  if (carouselContainer) {
    carouselContainer.addEventListener('mouseenter', function() {
      if (autoplayInterval) clearInterval(autoplayInterval);
    });
    carouselContainer.addEventListener('mouseleave', function() {
      startAutoplay();
    });
  }

  // Keyboard navigation for carousel
  document.addEventListener('keydown', function(e) {
    if (document.activeElement && document.activeElement.closest('.hero-carousel')) {
      if (e.key === 'ArrowLeft') {
        updateCarousel(currentSlide - 1);
        resetAutoplay();
      } else if (e.key === 'ArrowRight') {
        updateCarousel(currentSlide + 1);
        resetAutoplay();
      }
    }
  });

  // Legacy: Thumbnail click to change main image (fallback for old structure)
  document.querySelectorAll('.thumb[data-src]').forEach(function(t){
    t.addEventListener('click', function(){
      var src = this.getAttribute('data-src');
      var mainPhoto = document.getElementById('mainPhoto');
      if (mainPhoto) mainPhoto.src = src;
    });
  });

  

  // Accordion
  var accToggle = document.querySelector('.acc-toggle');
  var accPanel = document.querySelector('.acc-panel');
  accToggle.addEventListener('click', function(){
    if(accPanel.style.display === 'block'){ accPanel.style.display = 'none'; }
    else{ accPanel.style.display = 'block'; }
  });

  // Modal open/close
  var orderBtn = document.getElementById('orderBtn');
  var modal = document.getElementById('orderModal');
  var closeBtn = document.getElementById('closeModal');

  if(orderBtn){
    orderBtn.addEventListener('click', function(){
      modal.style.display = 'flex';
      modal.setAttribute('aria-hidden','false');
    });
  }
  // Attach modal open handler to any .order-trigger buttons (handles multiple buttons)
  var orderTriggers = document.querySelectorAll('.order-trigger');
  var savedScrollY = 0;
  if(orderTriggers && orderTriggers.length){
    orderTriggers.forEach(function(btn){
      btn.addEventListener('click', function(e){
        e.preventDefault();
        // If this is the small inline order button, scroll to the inline order form instead of opening modal
        if(btn.id === 'orderBtnSmall'){
          var inlineSection = document.getElementById('order-cta');
          if(inlineSection){
            inlineSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            // focus the first inline input after scroll
            setTimeout(function(){
              var nameInline = document.getElementById('name_inline');
              if(nameInline) nameInline.focus();
            }, 500);
          }
          return;
        }
        if(!modal) return;
        savedScrollY = window.scrollY;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden','false');
        document.body.classList.add('modal-open');
        document.body.style.top = '-' + savedScrollY + 'px';
        // focus the name input for quick entry (after modal visible)
        setTimeout(function(){
          var nameEl = document.getElementById('name');
          if(nameEl) nameEl.focus();
        }, 60);
      });
    });
  }

  // Close order modal helper (used by close button, overlay click, and Escape)
  function closeOrderModal(){
    if(!modal) return;
    modal.setAttribute('aria-hidden','true');
    try{ modal.style.display = 'none'; }catch(e){}
    document.body.classList.remove('modal-open');
    document.body.style.top = '';
    window.scrollTo(0, savedScrollY);
    // return focus to the primary order trigger (if present)
    var primary = document.querySelector('.order-trigger');
    if(primary && typeof primary.focus === 'function') primary.focus();
  }

  // close button should use the shared close function
  if(closeBtn){ closeBtn.addEventListener('click', closeOrderModal); }

  // clicking outside the modal content (on overlay) closes the modal
  if(modal){
    modal.addEventListener('click', function(e){
      if(e.target === modal){ closeOrderModal(); }
    });
  }

  // allow Escape to close the order modal
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
      if(modal && modal.getAttribute('aria-hidden') === 'false'){
        closeOrderModal();
      }
      // existing video modal handler remains (it also listens for Escape)
    }
  });
  if(closeBtn){ closeBtn.addEventListener('click', function(){
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden','true');
  }); }

  // Submit order form (POST to /api/create-order.php)
  var orderForm = document.getElementById('orderForm');
  var formMessage = document.getElementById('formMessage');
  // Shipping and total calculation
  var qtyInput = document.getElementById('qty');
  var shippingEl = document.getElementById('shipping');
  var totalEl = document.getElementById('orderTotal');
  var basePriceEl = document.getElementById('basePrice');

  // Inline order form elements (if present)
  var orderFormInline = document.getElementById('orderFormInline');
  var formMessageInline = document.getElementById('formMessageInline');
  var qtyInputInline = document.getElementById('qty_inline');
  var shippingElInline = document.getElementById('shipping_inline');
  var totalElInline = document.getElementById('orderTotal_inline');
  var basePriceElInline = document.getElementById('basePrice_inline');

  function parsePrice(text){
    return parseFloat((text||'').toString().replace(/[^0-9\.]/g, '')) || 0;
  }

  function updateTotals(){
    var base = basePriceEl ? parsePrice(basePriceEl.textContent || basePriceEl.innerText) : 0;
    var qty = qtyInput ? (parseInt(qtyInput.value || 1, 10) || 1) : 1;
    var area = 'inside';
    // prefer select#area if present, otherwise legacy radio inputs
    var areaSelectEl = orderForm ? orderForm.querySelector('#area') : null;
    if(areaSelectEl){ area = areaSelectEl.value || 'inside'; }
    else { area = (orderForm.querySelector('input[name="area"]:checked')||{}).value || 'inside'; }
    var ship = (area === 'inside') ? 60 : 120;
    shippingEl.textContent = '৳' + ship.toFixed(2);
    var total = base * qty + ship;
    totalEl.textContent = '৳' + total.toFixed(2);
  }

  // update totals for inline form (keeps inline UI in sync)
  function updateTotalsInline(){
    if(!basePriceElInline || !qtyInputInline || !shippingElInline || !totalElInline) return;
    var base = parsePrice(basePriceElInline.textContent || basePriceElInline.innerText);
    var qty = parseInt(qtyInputInline.value || 1, 10) || 1;
    var area = 'inside';
    var areaSelectInline = document.getElementById('area_inline');
    if(areaSelectInline) area = areaSelectInline.value || 'inside';
    else { area = (document.querySelector('#orderFormInline input[name="area"]:checked')||{}).value || 'inside'; }
    var ship = (area === 'inside') ? 60 : 120;
    shippingElInline.textContent = '৳' + ship.toFixed(2);
    var total = base * qty + ship;
    totalElInline.textContent = '৳' + total.toFixed(2);
  }

  if(qtyInput){ qtyInput.addEventListener('input', updateTotals); }
  // Quantity +/- buttons
  var qtyButtons = document.querySelectorAll('.qty-btn');
  if(qtyButtons && qtyButtons.length && qtyInput){
    qtyButtons.forEach(function(btn){
      btn.addEventListener('click', function(){
        var action = btn.getAttribute('data-action');
        var current = parseInt(qtyInput.value || 1, 10) || 1;
        if(action === 'increase') current = current + 1;
        else current = Math.max(1, current - 1);
        qtyInput.value = current;
        if(orderForm) updateTotals();
      });
    });
  }
  // wire change events for delivery area: support both select and radio inputs
  var areaSelect = document.getElementById('area');
  if(areaSelect){ areaSelect.addEventListener('change', updateTotals); }
  var areaRadios = document.querySelectorAll('input[name="area"]');
  if(areaRadios && areaRadios.length){
    areaRadios.forEach(function(r){ r.addEventListener('change', updateTotals); });
  }

  // Segmented toggle for delivery area (modal form - keeps select for form submission)
  (function(){
    var areaToggle = document.querySelector('#orderForm .area-toggle');
    var select = document.getElementById('area');
    if(!areaToggle) return;
    var opts = Array.from(areaToggle.querySelectorAll('.area-option'));
    if(!opts || !opts.length) return;

    function setActive(value){
      opts.forEach(function(b){
        var v = b.getAttribute('data-value');
        var active = (v === value);
        b.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
      if(select) select.value = value;
      if(orderForm) updateTotals();
    }

    // click handlers
    opts.forEach(function(b){
      b.addEventListener('click', function(){ setActive(b.getAttribute('data-value')); });
    });

    // initialize from select value, if present
    if(select){ setActive(select.value || select.options[0].value); }
    else { setActive(opts[0].getAttribute('data-value')); }
  })();

  // Segmented toggle for inline area control
  (function(){
    var areaToggleInline = document.querySelector('.area-toggle-inline');
    var selectInline = document.getElementById('area_inline');
    if(!areaToggleInline) return;
    var opts = Array.from(areaToggleInline.querySelectorAll('.area-option'));
    if(!opts || !opts.length) return;

    function setActiveInline(value){
      opts.forEach(function(b){
        var v = b.getAttribute('data-value');
        var active = (v === value);
        b.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
      if(selectInline) selectInline.value = value;
      updateTotalsInline();
    }

    opts.forEach(function(b){ b.addEventListener('click', function(){ setActiveInline(b.getAttribute('data-value')); }); });
    if(selectInline){ setActiveInline(selectInline.value || selectInline.options[0].value); }
    else { setActiveInline(opts[0].getAttribute('data-value')); }
  })();

  // wire inline qty and area events
  if(qtyInputInline){ qtyInputInline.addEventListener('input', updateTotalsInline); }
  var qtyButtonsInline = document.querySelectorAll('#orderFormInline .qty-btn');
  if(qtyButtonsInline && qtyButtonsInline.length && qtyInputInline){
    qtyButtonsInline.forEach(function(btn){
      btn.addEventListener('click', function(){
        var action = btn.getAttribute('data-action');
        var current = parseInt(qtyInputInline.value || 1, 10) || 1;
        if(action === 'increase') current = current + 1;
        else current = Math.max(1, current - 1);
        qtyInputInline.value = current;
        updateTotalsInline();
      });
    });
  }
  var areaSelectInline = document.getElementById('area_inline');
  if(areaSelectInline){ areaSelectInline.addEventListener('change', updateTotalsInline); }

  // initialize totals
  if(orderForm) updateTotals();
  updateTotalsInline();

  if(orderForm) orderForm.addEventListener('submit', function(e){
    e.preventDefault();    
    // Client-side validation: ensure required fields are filled
    var requiredFields = Array.from(orderForm.querySelectorAll('[required]'));
    var firstInvalid = null;
    for(var i=0;i<requiredFields.length;i++){
      var el = requiredFields[i];
      var val = (el.value || '').toString().trim();
      if(!val){ firstInvalid = el; break; }
    }
    if(firstInvalid){
      formMessage.textContent = 'অনুগ্রহ করে সব তথ্য পূরণ করুন';
      formMessage.style.color = '#b62810';
      try{ firstInvalid.focus(); }catch(e){}
      return;
    }
    
    formMessage.style.color = '';    formMessage.textContent = 'অর্ডার প্রক্রিয়াকরণ...';

    // update hidden or computed values
    var data = new FormData(orderForm);    // ensure product_id is present
    if(!data.get('product_id')) data.append('product_id', '1');    fetch('/api/create-order.php', {
      method: 'POST',
      body: data
    }).then(function(res){
      if(!res.ok) throw new Error('Network response not ok');
      return res.json();
    }).then(function(json){
      if(json.success){
        formMessage.textContent = 'অর্ডার গ্রহণ করা হয়েছে — আপনার অর্ডার আইডি: ' + (json.order_id || 'N/A');
        orderForm.reset();
      } else {
        formMessage.textContent = json.message || 'অর্ডার করা যায়নি, পরে চেষ্টা করুন';
      }
    }).catch(function(err){
      formMessage.textContent = 'সার্ভার এরর — ' + err.message;
    });
  });

  // Inline form submit (POST to /api/create-order.php) — similar behavior to modal form
  if(orderFormInline){
    orderFormInline.addEventListener('submit', function(e){
        e.preventDefault();

        // Client-side validation: ensure required inline fields are filled
        var requiredFields = Array.from(orderFormInline.querySelectorAll('[required]'));
        var firstInvalid = null;
        for(var i=0;i<requiredFields.length;i++){
          var el = requiredFields[i];
          var val = (el.value || '').toString().trim();
          if(!val){ firstInvalid = el; break; }
        }
        if(firstInvalid){
          if(formMessageInline) formMessageInline.textContent = 'অনুগ্রহ করে প্রথমে ইনপুটগুলো পূরণ করুন';
          try{ firstInvalid.focus(); }catch(e){}
          return;
        }

        if(formMessageInline) formMessageInline.textContent = 'অর্ডার প্রক্রিয়াকরণ...';

        var data = new FormData(orderFormInline);
      // make sure product id present
      if(!data.get('product_id')) data.append('product_id','1');
      fetch('/api/create-order.php', { method: 'POST', body: data }).then(function(res){
        if(!res.ok) throw new Error('Network response not ok');
        return res.json();
      }).then(function(json){
        if(json.success){
          if(formMessageInline) formMessageInline.textContent = 'অর্ডার গ্রহণ করা হয়েছে — আপনার অর্ডার আইডি: ' + (json.order_id || 'N/A');
          try{ orderFormInline.reset(); updateTotalsInline(); }catch(e){}
        } else {
          if(formMessageInline) formMessageInline.textContent = json.message || 'অর্ডার করা যায়নি, পরে চেষ্টা করুন';
        }
      }).catch(function(err){
        if(formMessageInline) formMessageInline.textContent = 'সার্ভার এরর — ' + err.message;
      });
    });
  }

  // WhatsApp button: build a structured order message from form fields
  var wa = document.getElementById('whatsappBtn');
  var wa2 = document.getElementById('whatsappBtn2');
  var waCard = document.getElementById('whatsappCardBtn');

  function buildWhatsAppText(){
    var titleEl = document.querySelector('.title');
    var title = titleEl ? titleEl.textContent.trim() : '';
    var name = (document.getElementById('name') && document.getElementById('name').value.trim()) || '';
    var phone = (document.getElementById('phone') && document.getElementById('phone').value.trim()) || '';
    var address = (document.getElementById('address') && document.getElementById('address').value.trim()) || '';
    var qty = (document.getElementById('qty') && document.getElementById('qty').value) || '1';
    var area = 'inside';
    var areaSel = document.getElementById('area');
    if(areaSel) area = areaSel.value || area;
    else { var checked = document.querySelector('input[name="area"]:checked'); if(checked) area = checked.value || area; }
    var total = (document.getElementById('orderTotal') && document.getElementById('orderTotal').textContent) || '';

    var parts = [];
    parts.push('অর্ডার অনুরোধ:');
    if(title) parts.push('পণ্যের নাম: ' + title);
    if(name) parts.push('নাম: ' + name);
    if(phone) parts.push('ফোন: ' + phone);
    if(address) parts.push('ঠিকানা: ' + address);
    parts.push('পরিমাণ: ' + qty);
    parts.push('ডেলিভারি এলাকা: ' + area);
    if(total) parts.push('মোট: ' + total);

    return encodeURIComponent(parts.join('\n'));
  }

  if(wa){ wa.addEventListener('click', function(){
    var phone = '+8801619703227';
    var text = buildWhatsAppText();
    window.open('https://wa.me/' + phone + '?text=' + text, '_blank');
  }); }

  if(wa2){ wa2.addEventListener('click', function(){
    var phone = '+8801619703227';
    var text = buildWhatsAppText();
    window.open('https://wa.me/' + phone + '?text=' + text, '_blank');
  }); }
  if(waCard){ waCard.addEventListener('click', function(){
    var phone = '+8801619703227';
    var titleEl = document.querySelector('.title') || document.querySelector('.pname');
    var title = titleEl ? titleEl.textContent.trim() : '';

    // Prefer inline form values, fall back to modal form
    var name = (document.getElementById('name_inline') && document.getElementById('name_inline').value.trim()) || (document.getElementById('name') && document.getElementById('name').value.trim()) || '';
    var phoneInput = (document.getElementById('phone_inline') && document.getElementById('phone_inline').value.trim()) || (document.getElementById('phone') && document.getElementById('phone').value.trim()) || '';
    var address = (document.getElementById('address_inline') && document.getElementById('address_inline').value.trim()) || (document.getElementById('address') && document.getElementById('address').value.trim()) || '';

    var qty = parseInt((document.getElementById('qty_inline') && document.getElementById('qty_inline').value) || (document.getElementById('qty') && document.getElementById('qty').value) || 1, 10) || 1;

    // area: check inline select/radios then modal
    var area = 'inside';
    var areaInlineSel = document.getElementById('area_inline');
    if(areaInlineSel) area = areaInlineSel.value || area;
    else { var checkedInline = document.querySelector('#orderFormInline input[name="area"]:checked'); if(checkedInline) area = checkedInline.value || area; }
    if(!area){ var areaSel = document.getElementById('area'); if(areaSel) area = areaSel.value || 'inside'; else { var checked = document.querySelector('input[name="area"]:checked'); if(checked) area = checked.value || 'inside'; } }

    // base, shipping, total: prefer inline elements then modal elements
    var base = 0;
    if(typeof basePriceElInline !== 'undefined' && basePriceElInline){ base = parsePrice(basePriceElInline.textContent || basePriceElInline.innerText); }
    if(!base && basePriceEl){ base = parsePrice(basePriceEl.textContent || basePriceEl.innerText); }

    var ship = 0;
    if(typeof shippingElInline !== 'undefined' && shippingElInline){ ship = parsePrice(shippingElInline.textContent || shippingElInline.innerText); }
    if(!ship && shippingEl){ ship = parsePrice(shippingEl.textContent || shippingEl.innerText); }
    if(!ship) { ship = (area === 'inside') ? 60 : 120; }

    var total = 0;
    if(typeof totalElInline !== 'undefined' && totalElInline){ total = parsePrice(totalElInline.textContent || totalElInline.innerText); }
    if(!total && totalEl){ total = parsePrice(totalEl.textContent || totalEl.innerText); }
    if(!total) { total = base * qty + ship; }

    var parts = ['অর্ডার অনুরোধ:'];
    if(title) parts.push('পণ্যের নাম: ' + title);
    if(name) parts.push('নাম: ' + name);
    if(phoneInput) parts.push('ফোন: ' + phoneInput);
    if(address) parts.push('ঠিকানা: ' + address);
    if(base) parts.push('মূল্য: ৳' + base.toFixed(2));
    parts.push('পরিমাণ: ' + qty);
    parts.push('ডেলিভারি এলাকা: ' + area);
    if(ship) parts.push('শিপিং: ৳' + ship.toFixed(2));
    parts.push('মোট: ৳' + total.toFixed(2));

    var text = encodeURIComponent(parts.join('\n'));
    window.open('https://wa.me/' + phone + '?text=' + text, '_blank');
  }); }
  var phoneOrderBtn = document.getElementById('phoneOrderBtn');
  if(phoneOrderBtn){ phoneOrderBtn.addEventListener('click', function(){ window.location.href = 'tel:+8801619703227'; }); }

  // Video modal handlers for local video playback
  var openVideo = document.getElementById('openVideo');
  var videoModal = document.getElementById('videoModal');
  var closeVideo = document.getElementById('closeVideo');
  var productVideo = document.getElementById('productVideo');
  var videoUnmute = document.getElementById('videoUnmute');
  var videoScrollY = 0;

  function openVideoModalLocal(){
    videoScrollY = window.scrollY;
    if(videoModal){ videoModal.setAttribute('aria-hidden','false'); }
    document.body.classList.add('modal-open');
    document.body.style.top = '-' + videoScrollY + 'px';
    if(productVideo){
      try{
        // ensure muted before calling play to satisfy autoplay policies
        productVideo.muted = true;
        productVideo.currentTime = 0;
        productVideo.play().catch(function(){});
      }catch(e){}
    }
  }

  function closeVideoModalLocal(){
    if(videoModal) videoModal.setAttribute('aria-hidden','true');
    if(productVideo){ try{ productVideo.pause(); productVideo.currentTime = 0; productVideo.muted = true; }catch(e){} }
    document.body.classList.remove('modal-open');
    document.body.style.top = '';
    window.scrollTo(0, videoScrollY);
  }

  if(openVideo){ openVideo.addEventListener('click', openVideoModalLocal); }
  if(closeVideo){ closeVideo.addEventListener('click', closeVideoModalLocal); }
  if(videoModal){ videoModal.addEventListener('click', function(e){ if(e.target === videoModal) closeVideoModalLocal(); }); }

  // Unmute button toggles sound (user gesture)
  if(videoUnmute){
    videoUnmute.addEventListener('click', function(){
      if(!productVideo) return;
      productVideo.muted = !productVideo.muted;
      videoUnmute.textContent = productVideo.muted ? 'Unmute' : 'Mute';
      if(!productVideo.muted){ try{ productVideo.play().catch(function(){}); }catch(e){} }
    });
  }

  document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ if(videoModal && videoModal.getAttribute('aria-hidden') === 'false'){ closeVideoModalLocal(); } } });
});

// Review gallery lightbox
document.addEventListener('DOMContentLoaded', function(){
  var thumbs = Array.from(document.querySelectorAll('.review-thumb'));
  var lightbox = document.getElementById('reviewLightbox');
  var lbImg = document.getElementById('lightboxImg');
  var lbCaption = document.getElementById('lightboxCaption');
  var closeLb = document.getElementById('closeLightbox');
  var lbPrev = document.getElementById('lbPrev');
  var lbNext = document.getElementById('lbNext');
  var currentIndex = 0;
  var lightboxScrollY = 0;

  if(!thumbs || thumbs.length === 0) return;

  function showLightboxAt(i){
    if(!thumbs || thumbs.length === 0) return;
    currentIndex = ((i % thumbs.length) + thumbs.length) % thumbs.length; // normalize
    var btn = thumbs[currentIndex];
    var src = btn.getAttribute('data-src');
    var capEl = btn.querySelector('.caption');
    var cap = capEl ? capEl.textContent : '';
    if(lbImg) lbImg.src = src;
    if(lbCaption) lbCaption.textContent = cap;
    if(lightbox) lightbox.setAttribute('aria-hidden','false');
    if(!document.body.classList.contains('modal-open')){
      lightboxScrollY = window.scrollY;
      document.body.classList.add('modal-open');
      document.body.style.top = '-' + lightboxScrollY + 'px';
    }
  }

  thumbs.forEach(function(btn, idx){
    btn.addEventListener('click', function(e){
      showLightboxAt(idx);
    });
  });

  function closeLightbox(){
    if(!lightbox) return;
    lightbox.setAttribute('aria-hidden','true');
    if(lbImg) lbImg.src = '';
    document.body.classList.remove('modal-open');
    document.body.style.top = '';
    window.scrollTo(0, lightboxScrollY);
  }

  if(closeLb) closeLb.addEventListener('click', closeLightbox);
  if(lightbox){ lightbox.addEventListener('click', function(e){ if(e.target === lightbox) closeLightbox(); }); }

  if(lbPrev){ lbPrev.addEventListener('click', function(e){ e.stopPropagation(); showLightboxAt(currentIndex - 1); }); }
  if(lbNext){ lbNext.addEventListener('click', function(e){ e.stopPropagation(); showLightboxAt(currentIndex + 1); }); }

  document.addEventListener('keydown', function(e){
    if(!lightbox || lightbox.getAttribute('aria-hidden') === 'true') return;
    if(e.key === 'Escape'){ closeLightbox(); return; }
    if(e.key === 'ArrowLeft'){ showLightboxAt(currentIndex - 1); }
    if(e.key === 'ArrowRight'){ showLightboxAt(currentIndex + 1); }
  });

  // Make review gallery swipeable on touch / pointer devices
  (function(){
    var gallery = document.querySelector('.review-gallery');
    if(!gallery) return;
    var isDown = false, startX = 0, scrollLeft = 0, isDragging = false;

    gallery.addEventListener('pointerdown', function(e){
      isDown = true;
      startX = e.clientX;
      scrollLeft = gallery.scrollLeft;
      isDragging = false;
      gallery.setPointerCapture && gallery.setPointerCapture(e.pointerId);
    });

    gallery.addEventListener('pointermove', function(e){
      if(!isDown) return;
      var x = e.clientX;
      var walk = startX - x; // positive -> scroll right
      if(Math.abs(walk) > 5) isDragging = true;
      gallery.scrollLeft = scrollLeft + walk;
    });

    ['pointerup','pointercancel','pointerleave'].forEach(function(evt){
      gallery.addEventListener(evt, function(e){ isDown = false; try{ gallery.releasePointerCapture && gallery.releasePointerCapture(e.pointerId); }catch(err){}; setTimeout(function(){ isDragging = false; }, 0); });
    });

    // prevent click activation when the user was dragging
    gallery.querySelectorAll('.review-thumb').forEach(function(btn){
      btn.addEventListener('click', function(e){ if(isDragging){ e.preventDefault(); e.stopImmediatePropagation(); } });
    });
  })();
});
