// =====================================================================
//  Lilia ilusalong — prototüübi ehitamise Figma plugin
//  Käivita failis "Lilia ilusalong — makett ja prototüüp":
//  Plugins → Development → Import plugin from manifest… → Lilia prototüüp
//  Ehitab lehele "2. Prototüüp" kõrgdetailsed ekraanid olemasolevatest
//  komponentidest ja seob need klikitavaks prototüübiks.
// =====================================================================

const C = { bg: '#fbf7f4', soft: '#f5ede7', ink: '#2f2430', muted: '#7c6f76', line: '#eadfd8', accent: '#9c5b74', dark: '#7a4159', aSoft: '#f3e3e8', gold: '#d9a441', ok: '#4f8a5b' };
const TONES = { 1: ['#f2d0db', '#9c5b74'], 2: ['#f7dac8', '#b0704b'], 3: ['#d9e5d3', '#5f7d5c'], 4: ['#e1d6ed', '#7a619a'], 5: ['#ece1d0', '#8c6f45'] };
const CAT = { 1: 'Juuksur', 2: 'Maniküür ja pediküür', 3: 'Näohooldus', 4: 'Meik ja kulmud', 5: 'Massaaž' };
const CAT_ICON = { 1: 'scissors', 2: 'hand', 3: 'leaf', 4: 'brush', 5: 'lotus' };
const SERVICES = [
  [1, 'Naiste juukselõikus', 60, 35, 1, '★★★★★ (2)'], [1, 'Meeste juukselõikus', 30, 20], [1, 'Juuste värvimine', 120, 70, 1], [1, 'Õhtusoeng', 60, 45],
  [2, 'Klassikaline maniküür', 45, 25], [2, 'Geellakkimine', 75, 35, 1, '★★★★☆ (1)'], [2, 'Pediküür', 60, 40],
  [3, 'Näopuhastus', 75, 50, 1, '★★★★★ (1)'], [3, 'Niisutav näohooldus', 60, 45],
  [4, 'Päevameik', 45, 30], [4, 'Kulmude korrigeerimine ja värvimine', 30, 18, 1, '★★★★☆ (1)'], [4, 'Pruutmeik', 90, 70],
  [5, 'Klassikaline seljamassaaž', 45, 35], [5, 'Lõõgastav aroomimassaaž', 60, 55, 1, '★★★★★ (1)']
];

// ---------------------------------------------------------------- abi
const hex = h => ({ r: parseInt(h.slice(1, 3), 16) / 255, g: parseInt(h.slice(3, 5), 16) / 255, b: parseInt(h.slice(5, 7), 16) / 255 });
const S = h => [{ type: 'SOLID', color: hex(h) }];
const stop = (p, h) => ({ position: p, color: Object.assign(hex(h), { a: 1 }) });
const SHADOW = [{ type: 'DROP_SHADOW', color: { r: 0.31, g: 0.18, b: 0.24, a: 0.08 }, offset: { x: 0, y: 10 }, radius: 30, spread: 0, visible: true, blendMode: 'NORMAL' }];

function AL(dir, props) {
  const f = figma.createFrame();
  f.layoutMode = dir;
  f.primaryAxisSizingMode = 'AUTO';
  f.counterAxisSizingMode = 'AUTO';
  f.fills = [];
  f.clipsContent = false;
  if (props) Object.assign(f, props);
  return f;
}
function fixedWidth(f, w) { f.resize(w, Math.max(f.height, 1)); f.counterAxisSizingMode = 'FIXED'; f.primaryAxisSizingMode = 'AUTO'; }
function fill(node) { node.layoutSizingHorizontal = 'FILL'; return node; }

function T(parent, txt, o) {
  o = o || {};
  const t = figma.createText();
  t.fontName = { family: o.serif ? 'Cormorant Garamond' : 'Manrope', style: o.serif ? 'SemiBold' : (o.w || 'Regular') };
  t.fontSize = o.size || 15;
  t.characters = txt;
  t.fills = S(o.color || C.ink);
  if (o.ls) t.letterSpacing = { unit: 'PERCENT', value: o.ls };
  if (o.upper) t.textCase = 'UPPER';
  if (o.lh) t.lineHeight = { unit: 'PERCENT', value: o.lh };
  parent.appendChild(t);
  if (o.width) { t.resize(o.width, t.height); t.textAutoResize = 'HEIGHT'; }
  if (o.fill) { t.layoutSizingHorizontal = 'FILL'; t.textAutoResize = 'HEIGHT'; }
  if (o.center) t.textAlignHorizontal = 'CENTER';
  return t;
}

function recolor(node, color) {
  const vecs = node.findAll(n => n.type === 'VECTOR' || n.type === 'ELLIPSE' || n.type === 'RECTANGLE');
  for (const v of vecs) if (v.strokes && v.strokes.length) v.strokes = S(color);
}

async function link(node, dest, transition) {
  if (!node || !dest) return;
  const reaction = {
    trigger: { type: 'ON_CLICK' },
    actions: [{
      type: 'NODE', destinationId: dest.id, navigation: 'NAVIGATE',
      transition: { type: transition || 'DISSOLVE', easing: { type: 'EASE_OUT' }, duration: 0.25 },
      preserveScrollPosition: false
    }]
  };
  if (typeof node.setReactionsAsync === 'function') await node.setReactionsAsync([reaction]);
  else node.reactions = [reaction];
}

// ---------------------------------------------------------------- põhiprogramm
async function main() {
  const page = figma.root.children.find(p => p.name === '2. Prototüüp');
  if (!page) throw new Error('Lehte "2. Prototüüp" ei leitud. Käivita plugin failis "Lilia ilusalong — makett ja prototüüp".');
  await figma.setCurrentPageAsync(page);

  await Promise.all([
    figma.loadFontAsync({ family: 'Cormorant Garamond', style: 'SemiBold' }),
    figma.loadFontAsync({ family: 'Manrope', style: 'Regular' }),
    figma.loadFontAsync({ family: 'Manrope', style: 'Medium' }),
    figma.loadFontAsync({ family: 'Manrope', style: 'SemiBold' }),
    figma.loadFontAsync({ family: 'Manrope', style: 'Bold' })
  ]);

  // Komponendid nime järgi (loodud varem samasse faili)
  const comp = name => page.findOne(n => (n.type === 'COMPONENT' || n.type === 'COMPONENT_SET') && n.name === name);
  const need = name => { const c = comp(name); if (!c) throw new Error('Komponenti "' + name + '" ei leitud lehelt "2. Prototüüp".'); return c; };
  const btnSet = need('Button');
  const variant = v => btnSet.children.find(c => c.name === 'Tüüp=' + v);
  const propKey = (owner, name) => Object.keys(owner.componentPropertyDefinitions).find(k => k.split('#')[0] === name);
  const btnP = variant('Primary'), btnG = variant('Ghost'), btnL = variant('Light');
  const BTN = propKey(btnSet, 'Tekst');
  const headerD = need('Header / Desktop'), headerM = need('Header / Mobiil');
  const footerD = need('Footer / Desktop'), footerM = need('Footer / Mobiil');
  const card = need('ServiceCard'), field = need('FormField'), review = need('ReviewCard'), master = need('MasterCard'), catCard = need('CategoryCard');
  const icon = name => need('icon/' + name);
  const K = (owner, name) => propKey(owner, name);

  // Vanad ekraanid eemaldatakse, et pluginat saaks uuesti käivitada
  for (const n of page.children.filter(n => /^(Desktop|Mobiil) \//.test(n.name))) n.remove();

  function btn(parent, text, v) { const b = (v || btnP).createInstance(); parent.appendChild(b); b.setProperties({ [BTN]: text }); return b; }
  function iconInst(parent, name, color, size) { const i = icon(name).createInstance(); i.resize(size, size); recolor(i, color); parent.appendChild(i); return i; }
  function svc(parent, s) {
    const i = card.createInstance(); parent.appendChild(i); i.name = 'ServiceCard / ' + s[1];
    const p = {};
    p[K(card, 'Kategooria')] = CAT[s[0]]; p[K(card, 'Pealkiri')] = s[1]; p[K(card, 'Kestus')] = s[2] + ' min'; p[K(card, 'Hind')] = s[3] + ' €';
    p[K(card, 'Populaarne')] = !!s[4]; p[K(card, 'Hinnang')] = s[5] || ' '; p[K(card, 'Ikoon')] = icon(CAT_ICON[s[0]]).id;
    i.setProperties(p);
    const m = i.findOne(n => n.name === 'media'); m.fills = S(TONES[s[0]][0]);
    const mi = m.findOne(n => n.name === 'media-icon'); if (mi) recolor(mi, TONES[s[0]][1]);
    return i;
  }
  function fieldInst(parent, label, value, height) {
    const f = field.createInstance(); parent.appendChild(f); f.layoutSizingHorizontal = 'FILL';
    const p = {}; p[K(field, 'Silt')] = label; p[K(field, 'Väärtus')] = value || ' '; f.setProperties(p);
    if (height) { const inp = f.findOne(n => n.name === 'input'); inp.counterAxisAlignItems = 'MIN'; inp.counterAxisSizingMode = 'FIXED'; inp.resize(inp.width, height); inp.layoutSizingHorizontal = 'FILL'; }
    return f;
  }
  function screen(name, x, y, mobile) {
    const s = AL('VERTICAL', { name: name });
    s.fills = S(C.bg); fixedWidth(s, mobile ? 390 : 1440); s.x = x; s.y = y; s.clipsContent = true;
    page.appendChild(s);
    const h = (mobile ? headerM : headerD).createInstance(); s.appendChild(h); h.layoutSizingHorizontal = 'FILL';
    return s;
  }
  function section(scr, title, soft, props, mobile) {
    const pad = mobile ? 20 : 130;
    const s = AL('VERTICAL', Object.assign({ itemSpacing: mobile ? 24 : 36, paddingLeft: pad, paddingRight: pad, paddingTop: mobile ? 48 : 76, paddingBottom: mobile ? 48 : 76, counterAxisAlignItems: 'CENTER' }, props || {}));
    if (soft) s.fills = S(C.soft);
    scr.appendChild(s); s.layoutSizingHorizontal = 'FILL';
    if (title) {
      const w = AL('VERTICAL', { itemSpacing: 12, counterAxisAlignItems: 'CENTER', name: 'section-title' }); s.appendChild(w);
      T(w, title, { serif: true, size: mobile ? 30 : 41.6, center: true });
      const ln = figma.createRectangle(); ln.resize(56, 3); ln.cornerRadius = 3; ln.fills = S(C.accent); w.appendChild(ln);
    }
    return s;
  }
  function row(parent, gap, align) { const r = AL('HORIZONTAL', { itemSpacing: gap }); if (align) r.counterAxisAlignItems = align; parent.appendChild(r); r.layoutSizingHorizontal = 'FILL'; return r; }
  function grid(parent, items, perRow, make, gap) {
    gap = gap || 24;
    const g = AL('VERTICAL', { itemSpacing: gap, name: 'grid' }); parent.appendChild(g); g.layoutSizingHorizontal = 'FILL';
    const made = [];
    for (let i = 0; i < items.length; i += perRow) {
      const r = row(g, gap);
      for (const it of items.slice(i, i + perRow)) { const n = make(r, it); n.layoutSizingHorizontal = 'FILL'; made.push(n); }
      for (let k = items.slice(i, i + perRow).length; k < perRow; k++) { const sp = figma.createFrame(); sp.fills = []; sp.resize(10, 10); r.appendChild(sp); sp.layoutSizingHorizontal = 'FILL'; }
    }
    return made;
  }
  function footer(scr, mobile) { const f = (mobile ? footerM : footerD).createInstance(); scr.appendChild(f); f.layoutSizingHorizontal = 'FILL'; return f; }
  function pageHead(scr, title, lead, mobile) {
    const ph = AL('VERTICAL', { itemSpacing: 10, paddingLeft: mobile ? 20 : 130, paddingRight: mobile ? 20 : 130, paddingTop: mobile ? 40 : 64, paddingBottom: 20, name: 'page-head' });
    ph.fills = [{ type: 'GRADIENT_LINEAR', gradientTransform: [[0, 1, 0], [-1, 0, 1]], gradientStops: [stop(0, C.soft), stop(1, C.bg)] }];
    scr.appendChild(ph); ph.layoutSizingHorizontal = 'FILL';
    T(ph, title, { serif: true, size: mobile ? 40 : 57.6, fill: mobile });
    if (lead) T(ph, lead, { size: 17.5, color: C.muted, width: mobile ? 350 : 620, lh: 160 });
    return ph;
  }
  function heroArt(parent, size) {
    const k = size / 420;
    const art = figma.createFrame(); art.name = 'hero-art'; art.resize(size, size); art.fills = []; parent.appendChild(art);
    const petal = (x, y, w, c, rot) => { const p = figma.createRectangle(); p.resize(w * k, w * k); p.topLeftRadius = 0; p.topRightRadius = w * k * 0.6; p.bottomRightRadius = 0; p.bottomLeftRadius = w * k * 0.6; p.fills = S(c); p.opacity = 0.8; p.x = x * k; p.y = y * k; p.rotation = rot; art.appendChild(p); };
    petal(30, 10, 160, '#f0d6de', 0); petal(250, 250, 160, '#e2ead9', 0); petal(320, 40, 100, '#f3e2d3', 20);
    const circ = figma.createEllipse(); circ.resize(320 * k, 320 * k); circ.x = 50 * k; circ.y = 50 * k;
    circ.fills = [{ type: 'GRADIENT_RADIAL', gradientTransform: [[1, 0, 0], [0, 1, 0]], gradientStops: [stop(0, '#ffffff'), stop(0.6, '#f6e4ea'), stop(1, '#ecd3dc')] }];
    circ.effects = [{ type: 'DROP_SHADOW', color: { r: 0.31, g: 0.18, b: 0.24, a: 0.16 }, offset: { x: 0, y: 18 }, radius: 40, spread: 0, visible: true, blendMode: 'NORMAL' }];
    art.appendChild(circ);
    const lot = icon('lotus').createInstance(); lot.resize(150 * k, 150 * k); lot.x = 135 * k; lot.y = 125 * k; recolor(lot, C.accent); art.appendChild(lot);
    return art;
  }
  function heroBg() { return [{ type: 'GRADIENT_LINEAR', gradientTransform: [[0.7, 0.7, 0], [-0.7, 0.7, 0.5]], gradientStops: [stop(0, '#f8e8ee'), stop(0.55, C.bg), stop(1, '#eef2ea')] }]; }
  function whiteCard(parent, props) { const c = AL('VERTICAL', Object.assign({ cornerRadius: 18 }, props || {})); c.fills = S('#ffffff'); c.effects = SHADOW; parent.appendChild(c); return c; }
  function categoryCards(parent, mobile) {
    const out = [];
    const items = [[1, 4], [2, 3], [3, 2], [4, 3], [5, 2]];
    const make = (r, it) => {
      const id = it[0], cnt = it[1];
      const c = catCard.createInstance(); r.appendChild(c);
      const p = {}; p[K(catCard, 'Nimi')] = CAT[id]; p[K(catCard, 'Arv')] = cnt + ' teenust'; p[K(catCard, 'Ikoon')] = icon(CAT_ICON[id]).id; c.setProperties(p);
      c.fills = S(TONES[id][0]); recolor(c.findOne(n => n.name === 'icon-circle'), TONES[id][1]);
      const cntText = c.findOne(n => n.type === 'TEXT' && n.characters.indexOf('teenust') >= 0); if (cntText) cntText.fills = S(TONES[id][1]);
      out.push(c); return c;
    };
    if (mobile) grid(parent, items, 2, make, 12); else { const r = row(parent, 24); for (const it of items) fill(make(r, it)); }
    return out;
  }

  const screens = {};
  const links = []; // [node, destinationKey]

  // ===================== DESKTOP / AVALEHT =====================
  figma.notify('Ehitan: Avaleht…', { timeout: 1500 });
  let s = screen('Desktop / Avaleht', 0, 0); screens.home = s;
  const hero = AL('VERTICAL', { itemSpacing: 48, paddingLeft: 130, paddingRight: 130, paddingTop: 96, paddingBottom: 56, name: 'hero' });
  hero.fills = heroBg(); s.appendChild(hero); hero.layoutSizingHorizontal = 'FILL';
  const hr = row(hero, 40, 'CENTER');
  const ht = AL('VERTICAL', { itemSpacing: 20 }); hr.appendChild(ht); ht.layoutSizingHorizontal = 'FILL';
  T(ht, 'Ilu ja hoolitsus ühes kohas', { w: 'Bold', size: 12.5, color: C.accent, ls: 18, upper: true });
  T(ht, 'Leia aeg iseendale', { serif: true, size: 62 });
  T(ht, 'Juuksur, maniküür, näohooldus, meik ja massaaž — kogenud meistrid ja rahulik õhkkond Tallinna kesklinnas.', { size: 18, color: C.muted, width: 560, lh: 160 });
  const ha = AL('HORIZONTAL', { itemSpacing: 13 }); ht.appendChild(ha);
  links.push([btn(ha, 'Broneeri aeg'), 'booking']); links.push([btn(ha, 'Vaata teenuseid →', btnG), 'services']);
  heroArt(hr, 420);
  const stats = row(hero, 16);
  for (const st of [['1200', '+ rahulolevat klienti'], ['14', 'teenust'], ['10', 'aastat kogemust']]) {
    const b = whiteCard(stats, { itemSpacing: 4, counterAxisAlignItems: 'CENTER', paddingTop: 21, paddingBottom: 21 }); b.layoutSizingHorizontal = 'FILL';
    T(b, st[0], { serif: true, size: 42, color: C.accent }); T(b, st[1], { size: 15, color: C.muted });
  }
  let sec = section(s, 'Teenuste kategooriad');
  for (const c of categoryCards(sec)) links.push([c, 'services']);
  sec = section(s, 'Populaarsed teenused', true);
  for (const c of grid(sec, SERVICES.filter(x => x[4]), 3, svc)) links.push([c, 'service']);
  links.push([btn(sec, 'Kõik teenused →', btnG), 'services']);
  sec = section(s, 'Miks Lilia?');
  const wr = row(sec, 24);
  for (const w of [['user', 'Kogenud meistrid', 'Iga meister on oma ala spetsialist ja täiendab end pidevalt.'], ['leaf', 'Kvaliteetsed tooted', 'Kasutame ainult professionaalseid ja nahasõbralikke tooteid.'], ['calendar', 'Online-broneering', 'Vali teenus, meister ja sobiv aeg vaid mõne klikiga.']]) {
    const box = AL('VERTICAL', { itemSpacing: 10, counterAxisAlignItems: 'CENTER', paddingTop: 24, paddingBottom: 24, paddingLeft: 24, paddingRight: 24 }); wr.appendChild(box); box.layoutSizingHorizontal = 'FILL';
    iconInst(box, w[0], C.accent, 44); T(box, w[1], { serif: true, size: 23 }); T(box, w[2], { size: 15, color: C.muted, fill: true, center: true, lh: 150 });
  }
  sec = section(s, 'Meie meistrid', true);
  const mr = row(sec, 24);
  const MASTERS = [['KT', 'Kristiina Tamm', 1, 'Juuksur, kes armastab naturaalseid toone ja lihtsalt hooldatavaid soenguid.', 8], ['OI', 'Olga Ivanova', 2, 'Maniküüri- ja pediküürimeister, kelle tööd on alati täpsed ja korralikud.', 6], ['MK', 'Maria Kask', 3, 'Kosmeetik, kes valib hoolduse iga kliendi nahatüübi järgi.', 10], ['AP', 'Anastassia Petrova', 4, 'Meigikunstnik ja kulmumeister, teeb nii päeva- kui ka pruutmeiki.', 5]];
  for (const m of MASTERS) {
    const i = master.createInstance(); mr.appendChild(i); i.layoutSizingHorizontal = 'FILL';
    const p = {}; p[K(master, 'Initsiaalid')] = m[0]; p[K(master, 'Nimi')] = m[1]; p[K(master, 'Kategooria')] = CAT[m[2]]; p[K(master, 'Kirjeldus')] = m[3]; p[K(master, 'Kogemus')] = m[4] + ' a. kogemust'; i.setProperties(p);
    const av = i.findOne(n => n.name === 'avatar'); av.fills = S(TONES[m[2]][0]); const at = av.findOne(n => n.type === 'TEXT'); if (at) at.fills = S(TONES[m[2]][1]);
  }
  btn(sec, 'Meistrid →', btnG);
  sec = section(s, 'Klientide arvustused');
  const rr = row(sec, 24, 'MIN');
  for (const r of [['★★★★☆', 'Kulmud said ilusa kuju, tulen kindlasti tagasi.', 'Triin · Kulmude korrigeerimine ja värvimine'], ['★★★★★', 'Лучший массаж в городе, очень расслабляющая атмосфера.', 'Marina · Lõõgastav aroomimassaaž'], ['★★★★★', 'Nahk on pärast hooldust palju puhtam ja rahulikum.', 'Kadri · Näopuhastus']]) {
    const i = review.createInstance(); rr.appendChild(i); i.layoutSizingHorizontal = 'FILL';
    const p = {}; p[K(review, 'Tähed')] = r[0]; p[K(review, 'Tekst')] = r[1]; p[K(review, 'Autor')] = r[2]; i.setProperties(p);
  }
  const cta = AL('HORIZONTAL', { paddingLeft: 130, paddingRight: 130, paddingTop: 56, paddingBottom: 56, primaryAxisAlignItems: 'SPACE_BETWEEN', counterAxisAlignItems: 'CENTER', name: 'cta-band' });
  cta.fills = [{ type: 'GRADIENT_LINEAR', gradientTransform: [[1, 0, 0], [0, 1, 0]], gradientStops: [stop(0, C.accent), stop(0.6, '#b9788f'), stop(1, '#c9967e')] }];
  s.appendChild(cta); cta.layoutSizingHorizontal = 'FILL';
  T(cta, 'Leia aeg iseendale', { serif: true, size: 41.6, color: '#ffffff' }); links.push([btn(cta, 'Broneeri aeg', btnL), 'booking']);
  footer(s);

  // ===================== DESKTOP / TEENUSED =====================
  figma.notify('Ehitan: Teenused…', { timeout: 1500 });
  s = screen('Desktop / Teenused', 1600, 0); screens.services = s;
  pageHead(s, 'Kõik teenused');
  sec = section(s, null, false, { paddingTop: 40 });
  const chips = AL('HORIZONTAL', { itemSpacing: 8, name: 'filters' }); sec.appendChild(chips);
  ['Kõik', 'Juuksur', 'Maniküür ja pediküür', 'Näohooldus', 'Meik ja kulmud', 'Massaaž'].forEach((c, i) => {
    const ch = AL('HORIZONTAL', { paddingLeft: 17, paddingRight: 17, paddingTop: 8, paddingBottom: 8, cornerRadius: 999, name: 'chip/' + c });
    ch.fills = S(i === 0 ? C.accent : '#ffffff'); if (i > 0) { ch.strokes = S(C.line); ch.strokeWeight = 1.5; }
    chips.appendChild(ch); T(ch, c, { w: 'SemiBold', size: 14, color: i === 0 ? '#ffffff' : C.ink });
  });
  for (const c of grid(sec, SERVICES, 3, svc)) links.push([c, 'service']);
  footer(s);

  // ===================== DESKTOP / TEENUSE LEHT =====================
  figma.notify('Ehitan: Teenuse leht…', { timeout: 1500 });
  s = screen('Desktop / Teenuse leht', 3200, 0); screens.service = s;
  sec = section(s, null, false, { counterAxisAlignItems: 'MIN' });
  const dr = row(sec, 56, 'CENTER');
  const media = AL('VERTICAL', { primaryAxisAlignItems: 'CENTER', counterAxisAlignItems: 'CENTER', cornerRadius: 24, name: 'detail-media' });
  media.fills = S(TONES[2][0]); media.effects = [{ type: 'DROP_SHADOW', color: { r: 0.31, g: 0.18, b: 0.24, a: 0.16 }, offset: { x: 0, y: 18 }, radius: 40, spread: 0, visible: true, blendMode: 'NORMAL' }];
  dr.appendChild(media); media.resize(562, 422); media.primaryAxisSizingMode = 'FIXED'; media.counterAxisSizingMode = 'FIXED'; media.layoutSizingHorizontal = 'FILL';
  iconInst(media, 'hand', TONES[2][1], 120);
  const info = AL('VERTICAL', { itemSpacing: 14 }); dr.appendChild(info); info.layoutSizingHorizontal = 'FILL';
  T(info, '← Maniküür ja pediküür', { w: 'SemiBold', size: 14.4, color: C.accent });
  T(info, 'Geellakkimine', { serif: true, size: 57.6 });
  T(info, '★★★★☆  4.0 (1)', { size: 14.4, color: C.gold });
  T(info, 'Maniküür ja püsiv geellakk, mis püsib kuni kolm nädalat. Suur värvivalik.', { size: 18, color: C.muted, fill: true, lh: 160 });
  const facts = AL('HORIZONTAL', { itemSpacing: 32, paddingTop: 6, paddingBottom: 10 }); info.appendChild(facts);
  for (const f of [['Hind', '35 €', true], ['Kestus', '75 min']]) { const fc = AL('VERTICAL', { itemSpacing: 2 }); facts.appendChild(fc); T(fc, f[0], { w: 'SemiBold', size: 12.5, color: C.muted, ls: 10, upper: true }); T(fc, f[1], f[2] ? { serif: true, size: 26, color: C.dark } : { w: 'SemiBold', size: 17.6 }); }
  links.push([btn(info, 'Broneeri'), 'booking']);
  sec = section(s, null, true, { counterAxisAlignItems: 'MIN' });
  const rl = row(sec, 40, 'MIN');
  const left = AL('VERTICAL', { itemSpacing: 16 }); rl.appendChild(left); left.layoutSizingHorizontal = 'FILL';
  T(left, 'Arvustused', { serif: true, size: 41.6 });
  const ok = AL('HORIZONTAL', { paddingLeft: 19, paddingRight: 19, paddingTop: 16, paddingBottom: 16, cornerRadius: 12 }); ok.fills = S('#e6f1e7'); left.appendChild(ok); ok.layoutSizingHorizontal = 'FILL'; T(ok, 'Aitäh arvustuse eest!', { w: 'SemiBold', size: 15, color: '#2f5f39' });
  const rv = review.createInstance(); left.appendChild(rv); rv.layoutSizingHorizontal = 'FILL';
  const rp = {}; rp[K(review, 'Tähed')] = '★★★★☆'; rp[K(review, 'Tekst')] = 'Покрытие держится уже две недели, всё аккуратно.'; rp[K(review, 'Autor')] = 'Jelena · 16.11.2026'; rv.setProperties(rp);
  const form = whiteCard(rl, { itemSpacing: 16, paddingLeft: 32, paddingRight: 32, paddingTop: 32, paddingBottom: 32, name: 'review-form' }); fixedWidth(form, 470);
  T(form, 'Jäta arvustus', { serif: true, size: 26 });
  fieldInst(form, 'Teie nimi', ' ');
  const rate = AL('VERTICAL', { itemSpacing: 4 }); form.appendChild(rate); T(rate, 'Hinne', { w: 'SemiBold', size: 14.4 }); T(rate, '★★★★★', { size: 30, color: C.gold });
  fieldInst(form, 'Arvustus', ' ', 110);
  btn(form, 'Saada arvustus');
  footer(s);

  // ===================== DESKTOP / BRONEERIMINE =====================
  figma.notify('Ehitan: Broneerimine…', { timeout: 1500 });
  s = screen('Desktop / Broneerimine', 4800, 0); screens.booking = s;
  pageHead(s, 'Online-broneering', 'Vali teenus, meister ja sobiv aeg. Võtame teiega broneeringu kinnitamiseks ühendust.');
  sec = section(s, null, false, { paddingTop: 48 });
  const bf = whiteCard(sec, { itemSpacing: 18, paddingLeft: 36, paddingRight: 36, paddingTop: 36, paddingBottom: 36, name: 'booking-form' }); fixedWidth(bf, 760);
  for (const pair of [[['Teenus *', 'Geellakkimine — 35 €  ⌄'], ['Meister *', 'Olga Ivanova (Maniküür ja pediküür)  ⌄']], [['Kuupäev *', '24.11.2026'], ['Kellaaeg *', '14:30  ⌄']], [['Nimi *', 'Mari Maasikas'], ['Telefon *', '+372 5555 1234']]]) {
    const r = row(bf, 16); for (const f of pair) fieldInst(r, f[0], f[1]);
  }
  const slots = AL('VERTICAL', { itemSpacing: 8, name: 'free-slots' }); bf.appendChild(slots);
  T(slots, 'Vabad ajad 24.11 (laaditakse AJAX-iga)', { w: 'SemiBold', size: 13, color: C.muted });
  const sr = AL('HORIZONTAL', { itemSpacing: 8 }); slots.appendChild(sr);
  for (const sl of [['13:00', 0], ['13:30', 0], ['14:00', 2], ['14:30', 1], ['15:00', 0], ['15:30', 2], ['16:00', 0]]) {
    const ch = AL('HORIZONTAL', { paddingLeft: 12, paddingRight: 12, paddingTop: 6, paddingBottom: 6, cornerRadius: 999 });
    ch.fills = S(sl[1] === 1 ? C.accent : (sl[1] === 2 ? '#f1ece9' : '#ffffff')); if (sl[1] === 0) { ch.strokes = S(C.line); ch.strokeWeight = 1.5; }
    sr.appendChild(ch); T(ch, sl[1] === 2 ? sl[0] + ' kinni' : sl[0], { w: 'SemiBold', size: 13, color: sl[1] === 1 ? '#ffffff' : (sl[1] === 2 ? '#b9adb3' : C.ink) });
  }
  fieldInst(bf, 'E-post (soovi korral)', ' ');
  fieldInst(bf, 'Lisainfo (soovi korral)', ' ', 90);
  const sb = btn(bf, 'Broneeri aeg'); sb.layoutSizingHorizontal = 'FILL'; links.push([sb, 'done']);
  footer(s);

  // ===================== DESKTOP / KINNITUS =====================
  s = screen('Desktop / Broneering kinnitatud', 6400, 0); screens.done = s;
  sec = section(s, null, false, { paddingTop: 90, paddingBottom: 110 });
  const okc = whiteCard(sec, { itemSpacing: 18, paddingLeft: 40, paddingRight: 40, paddingTop: 44, paddingBottom: 44, counterAxisAlignItems: 'CENTER', name: 'success-card' }); fixedWidth(okc, 600);
  const ci = AL('VERTICAL', { primaryAxisAlignItems: 'CENTER', counterAxisAlignItems: 'CENTER', cornerRadius: 999 }); ci.fills = S('#e6f1e7'); okc.appendChild(ci); ci.resize(84, 84); ci.primaryAxisSizingMode = 'FIXED'; ci.counterAxisSizingMode = 'FIXED';
  iconInst(ci, 'check', C.ok, 44);
  T(okc, 'Broneering on vastu võetud!', { serif: true, size: 40, center: true });
  const fl = AL('VERTICAL', { itemSpacing: 10, paddingLeft: 22, paddingRight: 22, paddingTop: 18, paddingBottom: 18, cornerRadius: 14 }); fl.fills = S(C.soft); okc.appendChild(fl); fl.layoutSizingHorizontal = 'FILL';
  for (const f of [['Teenus', 'Geellakkimine'], ['Meister', 'Olga Ivanova'], ['Kuupäev', '24.11.2026'], ['Kellaaeg', '14:30'], ['Hind', '35 €']]) {
    const r = AL('HORIZONTAL', { primaryAxisAlignItems: 'SPACE_BETWEEN' }); fl.appendChild(r); r.layoutSizingHorizontal = 'FILL';
    T(r, f[0], { w: 'SemiBold', size: 12.5, color: C.muted, ls: 10, upper: true }); T(r, f[1], { w: 'SemiBold', size: 16 });
  }
  T(okc, 'Ootame teid, Mari Maasikas!', { size: 16, color: C.muted });
  links.push([btn(okc, 'Avalehele'), 'home']);
  footer(s);

  // ===================== MOBIIL / AVALEHT =====================
  figma.notify('Ehitan: mobiilivaated…', { timeout: 1500 });
  const MY = 0, MX = 8000;
  s = screen('Mobiil / Avaleht', MX, MY, true); screens.mHome = s;
  const mh = AL('VERTICAL', { itemSpacing: 20, paddingLeft: 20, paddingRight: 20, paddingTop: 32, paddingBottom: 40, name: 'hero' }); mh.fills = heroBg(); s.appendChild(mh); mh.layoutSizingHorizontal = 'FILL';
  const artWrap = AL('VERTICAL', { counterAxisAlignItems: 'CENTER' }); mh.appendChild(artWrap); artWrap.layoutSizingHorizontal = 'FILL'; heroArt(artWrap, 260);
  T(mh, 'Ilu ja hoolitsus ühes kohas', { w: 'Bold', size: 11.5, color: C.accent, ls: 18, upper: true });
  T(mh, 'Leia aeg iseendale', { serif: true, size: 44, fill: true });
  T(mh, 'Juuksur, maniküür, näohooldus, meik ja massaaž — kogenud meistrid ja rahulik õhkkond Tallinna kesklinnas.', { size: 16.5, color: C.muted, fill: true, lh: 160 });
  const mb1 = btn(mh, 'Broneeri aeg'); mb1.layoutSizingHorizontal = 'FILL'; links.push([mb1, 'mBooking']);
  const mb2 = btn(mh, 'Vaata teenuseid →', btnG); mb2.layoutSizingHorizontal = 'FILL';
  for (const st of [['1200', '+ rahulolevat klienti'], ['14', 'teenust'], ['10', 'aastat kogemust']]) {
    const b = whiteCard(mh, { itemSpacing: 16, counterAxisAlignItems: 'CENTER', paddingTop: 14, paddingBottom: 14, paddingLeft: 18, paddingRight: 18 }); b.layoutMode = 'HORIZONTAL'; b.layoutSizingHorizontal = 'FILL';
    const n = T(b, st[0], { serif: true, size: 32, color: C.accent }); n.resize(80, n.height); T(b, st[1], { size: 15, color: C.muted });
  }
  sec = section(s, 'Kategooriad', false, null, true); categoryCards(sec, true);
  sec = section(s, 'Populaarsed teenused', true, null, true);
  for (const sv of SERVICES.filter(x => x[4]).slice(0, 3)) { const c = svc(sec, sv); c.layoutSizingHorizontal = 'FILL'; }
  footer(s, true);

  // ===================== MOBIIL / MENÜÜ =====================
  s = AL('VERTICAL', { name: 'Mobiil / Menüü avatud' }); s.fills = S(C.bg); s.resize(390, 844); s.primaryAxisSizingMode = 'FIXED'; s.counterAxisSizingMode = 'FIXED'; s.x = MX + 500; s.y = MY; s.clipsContent = true; page.appendChild(s); screens.mMenu = s;
  const mhd = AL('HORIZONTAL', { paddingLeft: 20, paddingRight: 20, paddingTop: 16, paddingBottom: 16, primaryAxisAlignItems: 'SPACE_BETWEEN', counterAxisAlignItems: 'CENTER', name: 'header-open' }); mhd.fills = S(C.bg); mhd.strokes = S(C.line); mhd.strokeTopWeight = 0; mhd.strokeLeftWeight = 0; mhd.strokeRightWeight = 0; mhd.strokeBottomWeight = 1; s.appendChild(mhd); mhd.layoutSizingHorizontal = 'FILL';
  const lg = AL('HORIZONTAL', { itemSpacing: 9, counterAxisAlignItems: 'CENTER' }); mhd.appendChild(lg); iconInst(lg, 'lotus', C.accent, 34); const lgt = AL('VERTICAL', { itemSpacing: 2 }); lg.appendChild(lgt); T(lgt, 'Lilia', { serif: true, size: 27 }); T(lgt, 'ilusalong', { w: 'Medium', size: 10.5, color: C.muted, ls: 14, upper: true });
  const closeI = iconInst(mhd, 'close', C.ink, 28); links.push([closeI, 'mHome']);
  const menu = AL('VERTICAL', { itemSpacing: 0, paddingLeft: 20, paddingRight: 20, paddingTop: 16, paddingBottom: 24 }); s.appendChild(menu); menu.layoutSizingHorizontal = 'FILL';
  const item = (txt, dest, chevron) => { const it = AL('HORIZONTAL', { itemSpacing: 6, paddingTop: 13, paddingBottom: 13, paddingLeft: 6, counterAxisAlignItems: 'CENTER' }); it.strokes = S(C.line); it.strokeTopWeight = 0; it.strokeLeftWeight = 0; it.strokeRightWeight = 0; it.strokeBottomWeight = 1; menu.appendChild(it); it.layoutSizingHorizontal = 'FILL'; T(it, txt, { w: 'Medium', size: 18.4 }); if (chevron) { const ch = iconInst(it, 'chevron', C.ink, 16); ch.rotation = 180; } if (dest) links.push([it, dest]); return it; };
  item('Avaleht', 'mHome'); item('Teenused', null, true);
  const subm = AL('VERTICAL', { itemSpacing: 2, paddingLeft: 14, paddingRight: 14, paddingTop: 8, paddingBottom: 8, cornerRadius: 14 }); subm.fills = S(C.soft); menu.appendChild(subm); subm.layoutSizingHorizontal = 'FILL';
  for (const t of ['Kõik teenused', 'Juuksur', 'Maniküür ja pediküür', 'Näohooldus', 'Meik ja kulmud', 'Massaaž']) { const st = AL('HORIZONTAL', { paddingTop: 9, paddingBottom: 9 }); subm.appendChild(st); T(st, t, { size: 16 }); }
  item('Meistrid'); item('Kontakt'); item('Registreeru');
  const tools = AL('VERTICAL', { itemSpacing: 14, paddingLeft: 20, paddingRight: 20 }); s.appendChild(tools); tools.layoutSizingHorizontal = 'FILL';
  const msf = AL('HORIZONTAL', { paddingLeft: 16, paddingRight: 12, paddingTop: 10, paddingBottom: 10, cornerRadius: 999, primaryAxisAlignItems: 'SPACE_BETWEEN', counterAxisAlignItems: 'CENTER' }); msf.fills = S('#ffffff'); msf.strokes = S(C.line); tools.appendChild(msf); msf.layoutSizingHorizontal = 'FILL'; T(msf, 'Otsi teenust...', { size: 15, color: C.muted }); iconInst(msf, 'search', C.accent, 18);
  const mls = AL('HORIZONTAL', { itemSpacing: 2, paddingLeft: 3, paddingRight: 3, paddingTop: 3, paddingBottom: 3, cornerRadius: 999 }); mls.fills = S(C.soft); tools.appendChild(mls);
  for (const l of [['ET', true], ['RU', false]]) { const p = AL('HORIZONTAL', { paddingLeft: 11, paddingRight: 11, paddingTop: 5, paddingBottom: 5, cornerRadius: 999 }); p.fills = l[1] ? S('#ffffff') : []; mls.appendChild(p); T(p, l[0], { w: 'Bold', size: 13, color: l[1] ? C.dark : C.muted }); }
  const mbb = btn(tools, 'Broneeri aeg'); mbb.layoutSizingHorizontal = 'FILL'; links.push([mbb, 'mBooking']);

  // ===================== MOBIIL / BRONEERIMINE =====================
  s = screen('Mobiil / Broneerimine', MX + 1000, MY, true); screens.mBooking = s;
  pageHead(s, 'Online-broneering', 'Vali teenus, meister ja sobiv aeg. Võtame teiega broneeringu kinnitamiseks ühendust.', true);
  sec = section(s, null, false, { paddingTop: 28 }, true);
  const mf = whiteCard(sec, { itemSpacing: 16, paddingLeft: 20, paddingRight: 20, paddingTop: 24, paddingBottom: 24 }); mf.layoutSizingHorizontal = 'FILL';
  for (const f of [['Teenus *', 'Geellakkimine — 35 €  ⌄'], ['Meister *', 'Olga Ivanova  ⌄'], ['Kuupäev *', '24.11.2026'], ['Kellaaeg *', '14:30  ⌄'], ['Nimi *', 'Mari Maasikas'], ['Telefon *', '+372 5555 1234'], ['E-post (soovi korral)', ' ']]) fieldInst(mf, f[0], f[1]);
  fieldInst(mf, 'Lisainfo (soovi korral)', ' ', 80);
  const msb = btn(mf, 'Broneeri aeg'); msb.layoutSizingHorizontal = 'FILL'; links.push([msb, 'mDone']);
  footer(s, true);

  // ===================== MOBIIL / KINNITUS =====================
  s = screen('Mobiil / Broneering kinnitatud', MX + 1500, MY, true); screens.mDone = s;
  sec = section(s, null, false, { paddingTop: 48, paddingBottom: 64 }, true);
  const mok = whiteCard(sec, { itemSpacing: 16, paddingLeft: 22, paddingRight: 22, paddingTop: 32, paddingBottom: 32, counterAxisAlignItems: 'CENTER' }); mok.layoutSizingHorizontal = 'FILL';
  const mci = AL('VERTICAL', { primaryAxisAlignItems: 'CENTER', counterAxisAlignItems: 'CENTER', cornerRadius: 999 }); mci.fills = S('#e6f1e7'); mok.appendChild(mci); mci.resize(72, 72); mci.primaryAxisSizingMode = 'FIXED'; mci.counterAxisSizingMode = 'FIXED'; iconInst(mci, 'check', C.ok, 38);
  T(mok, 'Broneering on vastu võetud!', { serif: true, size: 30, center: true, fill: true });
  T(mok, 'Geellakkimine · Olga Ivanova\n24.11.2026 kell 14:30 · 35 €', { size: 15, color: C.muted, center: true, fill: true, lh: 160 });
  const mhome = btn(mok, 'Avalehele'); mhome.layoutSizingHorizontal = 'FILL'; links.push([mhome, 'mHome']);
  footer(s, true);

  // ===================== PROTOTÜÜBI SEOSED =====================
  figma.notify('Seon prototüübi…', { timeout: 1500 });
  for (const l of links) await link(l[0], screens[l[1]]);
  // Komponentide sees olevad seosed (kehtivad kõigil eksemplaridel)
  const navMap = { 'nav/Avaleht': 'home', 'nav/Teenused': 'services' };
  for (const key of Object.keys(navMap)) await link(headerD.findOne(n => n.name === key), screens[navMap[key]]);
  await link(headerD.findOne(n => n.name === 'logo'), screens.home);
  await link(headerD.findOne(n => n.name === 'cta'), screens.booking);
  await link(headerM.findOne(n => n.name === 'burger'), screens.mMenu);
  await link(headerM.findOne(n => n.name === 'logo'), screens.mHome);
  const cardBtn = card.findOne(n => n.type === 'INSTANCE' && n.name.indexOf('Tüüp=') === 0);
  await link(cardBtn, screens.booking);

  page.flowStartingPoints = [
    { nodeId: screens.home.id, name: 'Desktop: avaleht → broneering' },
    { nodeId: screens.mHome.id, name: 'Mobiil: avaleht → broneering' }
  ];

  // Pealkiri lehele
  const title = figma.createText(); title.fontName = { family: 'Cormorant Garamond', style: 'SemiBold' }; title.fontSize = 64; title.characters = 'Lilia ilusalong — prototüüp'; title.x = 0; title.y = -170; page.appendChild(title);
  const subT = figma.createText(); subT.fontName = { family: 'Manrope', style: 'Regular' }; subT.fontSize = 20; subT.fills = S(C.muted); subT.characters = 'Kõrgdetailne klikitav prototüüp. Vajuta ▶ Present ja vali voog „Desktop“ või „Mobiil“.'; subT.x = 0; subT.y = -82; page.appendChild(subT);
  title.name = 'Pealkiri'; subT.name = 'Alapealkiri';

  // Makett: märkused paremale ülaossa, et need ei kataks logo
  const wf = figma.root.children.find(p => p.name.indexOf('Makett') >= 0);
  if (wf) {
    await figma.setCurrentPageAsync(wf);
    for (const fr of wf.children) {
      if (fr.type !== 'FRAME') continue;
      for (const ch of fr.children) if (ch.layoutPositioning === 'ABSOLUTE') { ch.x = fr.width - ch.width - 16; ch.y = 88; }
    }
    await figma.setCurrentPageAsync(page);
  }
  figma.viewport.scrollAndZoomIntoView(Object.keys(screens).map(k => screens[k]));
  return Object.keys(screens).length;
}

main()
  .then(n => figma.closePlugin('Valmis: ' + n + ' ekraani ja prototüübi seosed loodud.'))
  .catch(e => { console.error(e); figma.closePlugin('Viga: ' + (e && e.message ? e.message : e)); });
