// Mobile menu toggle
const mobileBtn = document.getElementById('mobileMenuBtn');
const mobileMenu = document.getElementById('mobileMenu');
if (mobileBtn && mobileMenu){
  mobileBtn.addEventListener('click', ()=>{
    mobileMenu.classList.toggle('hidden');
  });
}

// Year in footer
document.getElementById('year').textContent = new Date().getFullYear();

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



  // Activities
  const agrid = document.getElementById('activitiesGrid');
  if(agrid && Array.isArray(d.activities)){
    agrid.innerHTML = '';
    d.activities.forEach(a=>{
      const card = el('div','activity-card');
      card.append(el('p','font-semibold', a.title || ''), el('p','text-sm text-navy-900/70', a.desc || ''));
      agrid.append(card);
    });
  }

  // Publications
  const plist = document.getElementById('publicationsList');
  if(plist && Array.isArray(d.publications)){
    plist.innerHTML = '';
    d.publications.forEach(p=>{
      const li = el('li','pub-item');
      const tag = el('span','tag', p.year || '');
      li.append(tag, document.createTextNode(' '+ (p.text || '')));
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
