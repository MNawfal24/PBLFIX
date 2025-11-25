// Simple polling: check content version every 15s; if changed, reload page
let lastVersion = 0; let pollTimer = null;
function startAutoRefresh(v){
  if (typeof v === 'number') lastVersion = v;
  if (pollTimer) return;
  pollTimer = setInterval(async ()=>{
    try{
      const r = await fetch('data/content.json?_=' + Date.now(), {cache:'no-store'});
      if(!r.ok) return; const j = await r.json();
      if (j && j.version && j.version !== lastVersion){
        location.reload();
      }
    }catch(e){}
  }, 15000);
}
// Mobile menu toggle
const mobileBtn = document.getElementById('mobileMenuBtn');
const mobileMenu = document.getElementById('mobileMenu');
if (mobileBtn && mobileMenu){
  mobileBtn.addEventListener('click', ()=>{
    mobileMenu.classList.toggle('hidden');
  });
}

// Year in footer (guard if element exists)
const yearEl = document.getElementById('year');
if (yearEl) {
  yearEl.textContent = new Date().getFullYear();
}

// Smooth scroll for internal links
document.querySelectorAll('a[href^="#"]').forEach(a=>{
  a.addEventListener('click', (e)=>{
    const id = a.getAttribute('href');
    if(id.length>1){
      e.preventDefault();
      document.querySelector(id)?.scrollIntoView({behavior:'smooth',block:'start'});
      mobileMenu?.classList.add('hidden');
    }
  });
});

// Lightbox for gallery
const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightboxImg');
const lightboxClose = document.getElementById('lightboxClose');

function attachLightbox(){
  document.querySelectorAll('.gallery-img').forEach(img=>{
    img.addEventListener('click', ()=>{
      lightboxImg.src = img.src;
      lightbox.classList.remove('hidden');
    });
  });
}

lightboxClose?.addEventListener('click', ()=>lightbox.classList.add('hidden'));
lightbox?.addEventListener('click', (e)=>{
  if(e.target === lightbox) lightbox.classList.add('hidden');
});

// First-visit Mascot Greeter
const greeter = document.getElementById('greeterModal');
const greeterClose = document.getElementById('greeterClose');
const greeterStart = document.getElementById('greeterStart');
function hideGreeter(){ greeter?.classList.add('hidden'); }
function showGreeter(){ greeter?.classList.remove('hidden'); greeter?.classList.add('flex'); }
function shouldGreet(){
  const url = new URL(window.location.href);
  if (url.searchParams.get('greet') === '1') return true;
  return !localStorage.getItem('ba_greeted');
}
document.addEventListener('DOMContentLoaded', ()=>{
  if (shouldGreet()) showGreeter();
});
greeterClose?.addEventListener('click', ()=>{ localStorage.setItem('ba_greeted','1'); hideGreeter(); });
greeterStart?.addEventListener('click', ()=>{ localStorage.setItem('ba_greeted','1'); hideGreeter(); });

// Dynamic content rendering
async function loadContent(){
  try{
    const res = await fetch('data/content.json', {cache:'no-store'});
    if(!res.ok) throw new Error('Failed to load content');
    const data = await res.json();
    renderContent(data);
    // Start polling to auto-refresh when CMS updates version
    startAutoRefresh(data?.version || 0);
  }catch(err){
    // silently ignore in case file missing
    console.warn(err);
  }
}

function el(tag, cls, text){
  const e = document.createElement(tag);
  if(cls) e.className = cls;
  if(text !== undefined) e.textContent = text;
  return e;
}

function renderContent(d){
  // Branding
  const nameEl = document.getElementById('siteNameText');
  const logoEl = document.getElementById('siteLogoImg');
  if (nameEl && d.siteName) nameEl.textContent = d.siteName;
  if (logoEl){
    if (d.siteLogo){
      logoEl.src = d.siteLogo;
      logoEl.style.display = '';
    } else {
      // keep default asset logo if provided; otherwise hide
      // logoEl.style.display = 'none';
    }
  }
  // Vision
  const v = document.getElementById('visionText');
  if(v && d.vision) v.textContent = d.vision;

  // What We Do
  const w = document.getElementById('whatWeDoGrid');
  if(w && Array.isArray(d.whatWeDo)){
    w.innerHTML = '';
    d.whatWeDo.forEach(item=>{
      const card = el('div','mini-card');
      const ic = el('div','icon-circle', item.icon || '📊');
      const t = el('p','font-semibold', item.title || '');
      const desc = el('p','text-sm text-navy-900/70', item.desc || '');
      card.append(ic,t,desc);
      w.append(card);
    });
  }

  // Team
  const tgrid = document.getElementById('teamGrid');
  if(tgrid && Array.isArray(d.team)){
    tgrid.innerHTML = '';
    d.team.forEach(m=>{
      const card = el('div','person-card');
      const av = el('div','avatar');
      const name = el('p','font-semibold', m.name || '');
      const role = el('p','text-sm text-navy-900/70', m.role || '');
      card.append(av,name,role);
      tgrid.append(card);
    });
  }

  // Facilities
  const fgrid = document.getElementById('facilitiesGrid');
  if(fgrid && Array.isArray(d.facilities)){
    fgrid.innerHTML = '';
    d.facilities.forEach(txt=>{
      const card = el('div','facility-card', txt);
      fgrid.append(card);
    });
  }

  // Research
const rgrid = document.getElementById('researchGrid');
if (rgrid && Array.isArray(d.research)) {
  rgrid.innerHTML = '';
  d.research.forEach(r => {
    const card = el('div', 'research-card');
    const title = el('p', 'font-semibold text-navy-900', r.title || '');
    const desc = el('p', 'text-sm text-navy-900/70 mt-1', r.desc || '');
    card.append(title, desc);
    rgrid.append(card);
  });
}



  // Activities / News section rendered as news cards
  const newsGrid = document.getElementById('activitiesGrid');
  const newsItems = Array.isArray(d.news) ? d.news : [];
  window.__newsItems = newsItems;
  if (newsGrid && newsItems.length){
    newsGrid.innerHTML = '';
    newsItems.forEach((item, idx)=>{
      const card = el('div','news-card');
      card.dataset.index = String(idx);
      if (item.image){
        const img = el('img','news-image');
        img.src = item.image;
        img.alt = item.title || '';
        card.append(img);
      }
      const body = el('div','news-content');
      const title = el('h3','news-title', item.title || '');
      const meta = el('div','news-date', item.date ? new Date(item.date).toLocaleDateString('id-ID',{year:'numeric',month:'long',day:'numeric'}) : '');
      const excerpt = el('p','news-excerpt', item.excerpt || '');
      const more = el('span','read-more','Baca Selengkapnya →');
      body.append(title, meta, excerpt, more);
      card.append(body);
      card.addEventListener('click', ()=>{
        openNewsModal(idx);
      });
      newsGrid.append(card);
    });
  } else if (newsGrid && Array.isArray(d.activities)){
    newsGrid.innerHTML = '';
    d.activities.forEach(a=>{
      const card = el('div','activity-card');
      card.append(el('p','font-semibold', a.title || ''), el('p','text-sm text-navy-900/70', a.desc || ''));
      newsGrid.append(card);
    });
  }

  // Publications
  const plist = document.getElementById('publicationsList');
  if(plist && Array.isArray(d.publications)){
    plist.innerHTML = '';
    d.publications.forEach(p=>{
      const li = el('li','pub-item');
      const tag = el('span','tag', p.year || '');

      const text = p.text || '';
      const link = (p.sinta_link || '').trim();

      li.append(tag, document.createTextNode(' '));

      if (link) {
        const a = document.createElement('a');
        a.href = link;
        a.target = '_blank';
        a.rel = 'noopener noreferrer';
        a.textContent = text || link;
        li.appendChild(a);
      } else {
        li.appendChild(document.createTextNode(text));
      }

      plist.append(li);
    });
  }

  // Gallery
  // === Gallery Carousel ===
const gtrack = document.getElementById("galleryTrack");
const carousel = document.getElementById("galleryCarousel");
const prev = document.getElementById("galleryPrev");
const next = document.getElementById("galleryNext");

if (gtrack && Array.isArray(d.gallery)) {
  // Normalize src: support string items and '../uploads/' paths from CMS
  const normSrc = (it) => {
    let src = (typeof it === 'string') ? it : (it && it.src) ? it.src : '';
    if (src.startsWith('../uploads/')) src = src.replace('../', '');
    if (src.startsWith('./')) src = src.slice(2);
    return src;
  };

  gtrack.innerHTML = d.gallery.map(it => {
    const src = normSrc(it);
    const caption = (typeof it === 'object' && it) ? (it.caption || '') : '';
    return `
      <div class="flex-shrink-0" style="width:300px;">
        <img src="${src}" alt="Gallery image" width="300" height="300" loading="lazy"
          class="gallery-img rounded-xl shadow-md border border-sky-100 object-cover hover:scale-105 transition-transform" style="width:300px;height:300px;object-fit:cover;">
        <p class="mt-2 text-sm text-navy-900/80 text-center">${caption}</p>
      </div>
    `;
  }).join('');

  // Scroll-based carousel (scroll the container, not the track)
  const scrollStep = 320;
  const scroller = carousel ?? gtrack;
  prev?.addEventListener("click", () => {
    scroller.scrollBy({ left: -scrollStep, behavior: "smooth" });
  });
  next?.addEventListener("click", () => {
    scroller.scrollBy({ left: scrollStep, behavior: "smooth" });
  });

  // Re-attach lightbox to newly rendered images
  attachLightbox();
}

}
  


loadContent();

// ====== Peminjaman page logic (moved from inline script) ======
function initPeminjamanPage(){
  const noticeBox = document.getElementById('bookingNotice');
  const noticeText = document.getElementById('bookingNoticeText');
  const bookingFrame = document.getElementById('bookingFrame');
  const refreshBtn = document.getElementById('refreshCal');

  if (!bookingFrame) return; // bukan di halaman peminjaman

  // Load booking notice from content.json
  fetch('data/content.json', {cache:'no-store'})
    .then(r => r.ok ? r.json() : null)
    .then(j => {
      if (j && j.bookingNotice && noticeBox && noticeText){
        noticeText.textContent = j.bookingNotice;
        noticeBox.style.display = 'block';
      }
    }).catch(()=>{});

  // Tombol refresh iframe jadwal
  if (refreshBtn){
    refreshBtn.addEventListener('click', ()=>{
      const url = new URL(bookingFrame.src, window.location.href);
      url.searchParams.set('ts', Date.now().toString());
      bookingFrame.src = url.toString();
    });
  }
}

// Gallery navigation
function initGalleryNavigation() {
  const galleryGrid = document.getElementById('galleryGrid');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  
  if (!galleryGrid || !prevBtn || !nextBtn) return;
  
  const galleryItems = Array.from(galleryGrid.children);
  if (galleryItems.length === 0) return;
  
  let itemWidth = galleryItems[0].offsetWidth + 16; // Width + gap
  let currentIndex = 0;
  let maxVisibleItems = Math.floor(galleryGrid.parentElement.offsetWidth / itemWidth);
  let maxIndex = Math.max(0, galleryItems.length - maxVisibleItems);
  
  // Function to update gallery position
  function updateGallery() {
    // Pastikan currentIndex tetap dalam batas yang valid
    currentIndex = Math.max(0, Math.min(currentIndex, maxIndex));
    
    // Hitung offset yang tepat berdasarkan item yang terlihat
    const offset = currentIndex * itemWidth;
    galleryGrid.style.transform = `translateX(-${offset}px)`;
    
    // Update tombol navigasi (hanya visual, tidak dinonaktifkan)
    prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
    nextBtn.style.opacity = currentIndex >= maxIndex ? '0.5' : '1';
  }
  
  // Event listeners for navigation buttons
  prevBtn.addEventListener('click', () => {
    if (currentIndex > 0) {
      currentIndex--;
      updateGallery();
    }
  });
  
  nextBtn.addEventListener('click', () => {
    if (currentIndex < maxIndex) {
      currentIndex++;
      updateGallery();
    }
  });
  
  // Initialize gallery
  galleryGrid.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
  
  // Handle window resize
  let resizeTimer;
  function handleResize() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      itemWidth = galleryItems[0].offsetWidth + 16;
      maxVisibleItems = Math.floor(galleryGrid.parentElement.offsetWidth / itemWidth);
      maxIndex = Math.max(0, galleryItems.length - maxVisibleItems);
      updateGallery();
    }, 250);
  }
  
  // Initialize and set up event listeners
  window.addEventListener('resize', handleResize);
  window.addEventListener('load', () => {
    // Tunggu sampai semua gambar selesai dimuat
    const images = galleryGrid.querySelectorAll('img');
    let loadedImages = 0;
    
    const checkAllLoaded = () => {
      loadedImages++;
      if (loadedImages === images.length) {
        handleResize();
      }
    };
    
    images.forEach(img => {
      if (img.complete) {
        checkAllLoaded();
      } else {
        img.addEventListener('load', checkAllLoaded);
      }
    });
    
    if (images.length === 0) handleResize();
  });
  
  // Initial update
  updateGallery();
}

// Initialize gallery carousel
function initGalleryCarousel() {
  const galleryTrack = document.getElementById('galleryTrack');
  const prevBtn = document.getElementById('galleryPrev');
  const nextBtn = document.getElementById('galleryNext');
  
  if (!galleryTrack || !prevBtn || !nextBtn) return;
  
  const galleryItems = Array.from(galleryTrack.children);
  if (galleryItems.length === 0) return;
  
  let currentIndex = 0;
  const itemWidth = galleryItems[0].offsetWidth + 16; // Width + gap
  let maxVisibleItems = Math.floor(galleryTrack.parentElement.offsetWidth / itemWidth);
  let maxIndex = Math.max(0, galleryItems.length - maxVisibleItems);
  
  // Function to update gallery position
  function updateGallery() {
    // Ensure currentIndex stays within valid bounds
    currentIndex = Math.max(0, Math.min(currentIndex, maxIndex));
    
    // Calculate the exact offset based on visible items
    const offset = currentIndex * itemWidth;
    galleryTrack.style.transform = `translateX(-${offset}px)`;
    
    // Update navigation buttons (visual feedback only, not disabled)
    prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
    nextBtn.style.opacity = currentIndex >= maxIndex ? '0.5' : '1';
  }
  
  // Event listeners for navigation buttons
  prevBtn.addEventListener('click', () => {
    if (currentIndex > 0) {
      currentIndex--;
      updateGallery();
    }
  });
  
  nextBtn.addEventListener('click', () => {
    if (currentIndex < maxIndex) {
      currentIndex++;
      updateGallery();
    }
  });
  
  // Initialize gallery
  galleryTrack.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
  
  // Handle window resize
  let resizeTimer;
  function handleResize() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      maxVisibleItems = Math.floor(galleryTrack.parentElement.offsetWidth / itemWidth);
      maxIndex = Math.max(0, galleryItems.length - maxVisibleItems);
      updateGallery();
    }, 250);
  }
  
  window.addEventListener('resize', handleResize);
  
  // Initial update
  updateGallery();
}

// Fungsi untuk membuka popup berita
function openNewsModal(index) {
  const modal = document.getElementById('newsModal');
  const content = document.getElementById('newsModalContent');
  if (!modal || !content) return;
  const list = window.__newsItems || [];
  const newsItem = list[index];
  if (!newsItem) return;

  const formattedDate = newsItem.date ? new Date(newsItem.date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }) : '';
  const rawBody = (newsItem.content || '').replace(/\r\n/g, '\n');
  const bodyHtml = rawBody
    .split('\n')
    .map(p => p.trim())
    .filter(p => p.length)
    .map(p => `<p>${p}</p>`)
    .join('');

  content.innerHTML = `
    <div class="news-modal-content">
      ${newsItem.image ? `<img src="${newsItem.image}" alt="${newsItem.title || ''}" class="news-modal-image">` : ''}
      <h1 class="news-modal-title">${newsItem.title || ''}</h1>
      <div class="news-modal-meta">
        <span>${formattedDate}</span>
      </div>
      <div class="news-modal-body">
        ${bodyHtml}
      </div>
    </div>
  `;
  // Tampilkan popup dengan kelas .active (CSS mengatur opacity/visibility)
  modal.classList.add('active');
}

// Tutup popup
document.getElementById('closeNewsModal')?.addEventListener('click', () => {
  const modal = document.getElementById('newsModal');
  if (!modal) return;
  modal.classList.remove('active');
});

// Tutup popup saat mengklik di luar konten
document.getElementById('newsModal')?.addEventListener('click', (e) => {
  const modal = document.getElementById('newsModal');
  if (!modal) return;
  if (e.target === modal) {
    const closeBtn = document.getElementById('closeNewsModal');
    if (closeBtn) closeBtn.click();
  }
});

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  initPeminjamanPage();
  initGalleryNavigation();
  initGalleryCarousel();
  attachLightbox();
  
  // Pastikan konten dimuat setelah DOM selesai dimuat
  if (typeof loadContent === 'function') {
    loadContent();
  }
});
