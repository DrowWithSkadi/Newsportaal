/* =====================================================================
   Lilia ilusalong — dünaamilised elemendid (puhas JavaScript)
   1. Mobiilimenüü (burger) ja alammenüü
   2. Päise vari kerimisel + "Üles" nupp
   3. Elementide ilmumine kerimisel (IntersectionObserver)
   4. Numbrite loendur avalehel
   5. Teenuste filtreerimine kategooria järgi
   6. Broneerimisvorm: meistrite filter ja vabad ajad (AJAX)
   7. Nuppude lainetuse (ripple) efekt
   ===================================================================== */
document.documentElement.classList.add('js');

document.addEventListener('DOMContentLoaded', function () {

    /* 1. Mobiilimenüü */
    var burger = document.querySelector('.burger');
    var nav = document.getElementById('main-nav');
    if (burger && nav) {
        burger.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            burger.setAttribute('aria-expanded', open);
            document.body.classList.toggle('nav-open', open);
        });
    }
    document.querySelectorAll('.has-sub > a').forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (window.innerWidth <= 900) {
                e.preventDefault();
                link.parentElement.classList.toggle('open');
            }
        });
    });

    /* 2. Päis ja "Üles" nupp */
    var header = document.querySelector('.site-header');
    var toTop = document.querySelector('.to-top');
    function onScroll() {
        var y = window.scrollY;
        if (header) header.classList.toggle('scrolled', y > 10);
        if (toTop) toTop.classList.toggle('show', y > 500);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    if (toTop) {
        toTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* 3. Ilmumine kerimisel */
    var revealItems = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealItems.forEach(function (el, i) {
            el.style.transitionDelay = (i % 4) * 0.08 + 's';
            observer.observe(el);
        });
    } else {
        revealItems.forEach(function (el) { el.classList.add('visible'); });
    }

    /* 4. Loendur (0 -> sihtväärtus) */
    var counters = document.querySelectorAll('.counter');
    function animateCounter(el) {
        var target = parseInt(el.dataset.target, 10) || 0;
        var start = null;
        var duration = 1600;
        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(target * eased);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    if (counters.length && 'IntersectionObserver' in window) {
        var counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        });
        counters.forEach(function (c) { counterObserver.observe(c); });
    } else {
        counters.forEach(function (c) { c.textContent = c.dataset.target; });
    }

    /* 5. Teenuste filter */
    var chips = document.querySelectorAll('.chip[data-filter]');
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            chips.forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
            var filter = chip.dataset.filter;
            document.querySelectorAll('.service-card').forEach(function (card) {
                var show = filter === 'all' || card.dataset.category === filter;
                card.classList.toggle('hidden', !show);
                card.classList.remove('fade-in');
                if (show) {
                    void card.offsetWidth; // animatsiooni taaskäivitamine
                    card.classList.add('fade-in', 'visible');
                }
            });
        });
    });

    /* 6. Broneerimisvorm */
    var serviceSel = document.getElementById('service');
    var masterSel = document.getElementById('master');
    var dateInput = document.getElementById('date');
    var timeSel = document.getElementById('time');

    if (serviceSel && masterSel && dateInput && timeSel) {
        var takenLabel = timeSel.dataset.takenLabel || '';
        var placeholder = timeSel.options[0].textContent;

        // Näita ainult valitud teenuse kategooria meistreid
        function filterMasters() {
            var opt = serviceSel.options[serviceSel.selectedIndex];
            var cat = opt ? opt.dataset.category : null;
            var firstMatch = null;
            Array.prototype.forEach.call(masterSel.options, function (o) {
                if (!o.value) return;
                var match = !cat || o.dataset.category === cat;
                o.hidden = !match;
                o.disabled = !match;
                if (match && !firstMatch) firstMatch = o;
            });
            var current = masterSel.options[masterSel.selectedIndex];
            if (!current || current.disabled || !current.value) {
                masterSel.value = firstMatch ? firstMatch.value : '';
            }
        }

        // Laadi serverist valitud meistri vabad ajad
        function loadSlots() {
            var master = masterSel.value;
            var date = dateInput.value;
            var previous = timeSel.value;
            if (!master || !date) return;
            timeSel.classList.add('loading');
            fetch('slots?master=' + encodeURIComponent(master) + '&date=' + encodeURIComponent(date))
                .then(function (r) { return r.json(); })
                .then(function (slots) {
                    timeSel.innerHTML = '';
                    var first = new Option(placeholder, '');
                    timeSel.add(first);
                    slots.forEach(function (s) {
                        var o = new Option(s.time + (s.free ? '' : ' — ' + takenLabel), s.time);
                        o.disabled = !s.free;
                        if (s.free && s.time === previous) o.selected = true;
                        timeSel.add(o);
                    });
                    if (!slots.length) first.textContent = '—';
                })
                .finally(function () { timeSel.classList.remove('loading'); });
        }

        serviceSel.addEventListener('change', function () { filterMasters(); loadSlots(); });
        masterSel.addEventListener('change', loadSlots);
        dateInput.addEventListener('change', loadSlots);
        filterMasters();
        loadSlots();
    }

    /* 7. Ripple efekt nuppudel */
    document.querySelectorAll('.btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var rect = btn.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height);
            var circle = document.createElement('span');
            circle.className = 'ripple';
            circle.style.width = circle.style.height = size + 'px';
            circle.style.left = (e.clientX - rect.left - size / 2) + 'px';
            circle.style.top = (e.clientY - rect.top - size / 2) + 'px';
            btn.appendChild(circle);
            setTimeout(function () { circle.remove(); }, 600);
        });
    });
});
