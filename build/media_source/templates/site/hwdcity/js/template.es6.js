/**
 * @package     Joomla.Site
 * @subpackage  Templates.Hwcity
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */

Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  function initTemplate(event) {
    const target = event && event.target ? event.target : document;

    /**
     * Prevent clicks on buttons within a disabled fieldset
     */
    target.querySelectorAll('fieldset.btn-group').forEach((fieldset) => {
      if (fieldset.getAttribute('disabled') === true) {
        fieldset.style.pointerEvents = 'none';
        fieldset.querySelectorAll('.btn').forEach((btn) => btn.classList.add('disabled'));
      }
    });
  }

  document.addEventListener('DOMContentLoaded', (event) => {
    initTemplate(event);

    /**
     * Back to top
     */
    const backToTop = document.getElementById('back-top');

    function checkScrollPos() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        backToTop.classList.add('visible');
      } else {
        backToTop.classList.remove('visible');
      }
    }

    if (backToTop) {
      checkScrollPos();

      window.addEventListener('scroll', checkScrollPos);

      backToTop.addEventListener('click', (ev) => {
        ev.preventDefault();
        window.scrollTo(0, 0);
      });
    }

    document.head.querySelectorAll('link[rel="lazy-stylesheet"]').forEach(($link) => {
      $link.rel = 'stylesheet';
    });
  });

  /**
   * Initialize the HWD menu
   */
  document.addEventListener('DOMContentLoaded', () => {
    const isMobile = () => window.innerWidth < 992;

    // Mobile click toggle logic
    document.querySelectorAll('.mod-menu .parent > a, .mod-menu .parent > button').forEach((toggler) => {
      toggler.addEventListener('click', (e) => {
        if (!isMobile()) return;

        e.preventDefault();

        const li = toggler.closest('li.parent');
        const icon = toggler.querySelector('i');

        if (li.classList.contains('open')) {
          li.classList.remove('open');
          if (icon) {
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-angle-down');
          }
        } else {
          document.querySelectorAll('.mod-menu li.parent.open').forEach((openLi) => {
            openLi.classList.remove('open');
            const openIcon = openLi.querySelector('i');
            if (openIcon) {
              openIcon.classList.remove('fa-xmark');
              openIcon.classList.add('fa-angle-down');
            }
          });

          li.classList.add('open');
          if (icon) {
            icon.classList.remove('fa-angle-down');
            icon.classList.add('fa-xmark');
          }
        }
      });
    });

    // Desktop hover icon swap
    if (!isMobile()) {
      document.querySelectorAll('.mod-menu li.parent').forEach((li) => {
        const icon = li.querySelector('i');

        li.addEventListener('mouseenter', () => {
          if (icon && icon.classList.contains('fa-angle-down')) {
            icon.classList.replace('fa-angle-down', 'fa-xmark');
          }
        });

        li.addEventListener('mouseleave', () => {
          if (icon && icon.classList.contains('fa-xmark')) {
            icon.classList.replace('fa-xmark', 'fa-angle-down');
          }
        });
      });
    }
  });

  // Menu scroll
  document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('.container-nav');
    if (!nav) return;

    const triggerEl = document.querySelector('.container-top-b,.page-start');
    const mq = window.matchMedia('(min-width: 992px)'); // desktop only

    let triggerY = 0;

    function computeTrigger() {
      if (!triggerEl) {
        triggerY = 0;
        return;
      }
      const r = triggerEl.getBoundingClientRect();
      triggerY = window.pageYOffset + r.top;
    }

    let lastY = window.pageYOffset || 0;
    const threshold = 6;
    const minTop = 32;
    const isMenuOpen = () => !!document.querySelector('.navbar-collapse.show');

    let ticking = false;

    function onScroll() {
      if (!mq.matches) return; // disable on tablets/phones
      if (ticking) return;
      ticking = true;

      requestAnimationFrame(() => {
        const y = window.pageYOffset || 0;
        const dy = y - lastY;
        const beforeTrigger = y < Math.max(triggerY, minTop);

        if (beforeTrigger || isMenuOpen()) {
          nav.classList.remove('nav-hide');
        } else {
          if (dy > threshold) nav.classList.add('nav-hide'); // down -> hide
          if (dy < -threshold) nav.classList.remove('nav-hide'); // up -> show
        }

        lastY = y;
        ticking = false;
      });
    }

    function enableDesktopMode() {
      computeTrigger();
      lastY = window.pageYOffset || 0;
      onScroll(); // sync state immediately
    }

    function disableMobileMode() {
      // ensure nav is visible when leaving desktop
      nav.classList.remove('nav-hide');
    }

    // Init + listeners
    enableDesktopMode();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', () => {
      if (mq.matches) enableDesktopMode();
      else disableMobileMode();
    });
    window.addEventListener('load', () => {
      if (mq.matches) enableDesktopMode();
    });

    // React to breakpoint changes immediately
    if (mq.addEventListener) {
      mq.addEventListener('change', (e) => (e.matches ? enableDesktopMode() : disableMobileMode()));
    } else if (mq.addListener) { // older browsers
      mq.addListener((e) => (e.matches ? enableDesktopMode() : disableMobileMode()));
    }

    // Keep trigger updated if that section’s height changes
    if ('ResizeObserver' in window && triggerEl) {
      new ResizeObserver(() => {
        if (mq.matches) computeTrigger();
      }).observe(triggerEl);
    }
  });

  // Offcanvas click handler
  document.addEventListener('click', (e) => {
    const link = e.target.closest('.offcanvas a.d-block');
    if (!link) return;

    const list = link.closest('ul').querySelectorAll('a.d-block');

    // Reset all to default inactive state
    list.forEach((a) => {
      a.classList.remove('bg-danger', 'text-white');
      a.classList.add('text-light');
    });

    // Highlight the clicked link
    link.classList.remove('text-light');
    link.classList.add('bg-danger', 'text-white');
  });

  // Read Progress Bar (outside Bar B)
  document.addEventListener('DOMContentLoaded', () => {
    const track = document.getElementById('readProgressTrack');
    const bar = document.getElementById('readProgressBar');
    const compact = document.getElementById('compactBar'); // Bar B (fixed under main)
    if (!track || !bar) return;

    // Find the end-of-article marker
    const endEl = document.querySelector('#comment, #comments'); // try either id
    let endY = 0;

    function getDocY(el) {
      if (!el) return 0;
      const r = el.getBoundingClientRect();
      return (window.pageYOffset || document.documentElement.scrollTop || 0) + r.top;
    }

    function computeEnd() {
      // If comments exist, stop at their top; else use full page height
      endY = endEl
        ? getDocY(endEl)
        : Math.max(
          document.documentElement.scrollHeight,
          document.body.scrollHeight
        );
    }

    // Place the track right below the compact bar
    function placeTrack() {
      const h = compact ? compact.offsetHeight : 0;
      track.style.top = `${h}px`;
    }

    function computeProgress() {
      const doc = document.documentElement;
      const scrollTop = window.pageYOffset || doc.scrollTop || 0;
      const viewport = doc.clientHeight;

      // Max scrollable distance until the comments (or full page)
      const max = Math.max(endY - viewport, 0);
      const pct = max ? Math.min(scrollTop / max, 1) : 0;
      bar.style.transform = `scaleX(${pct})`;
    }

    let ticking = false;
    function onScroll() {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => {
        computeProgress();
        ticking = false;
      });
    }

    // Init + listeners
    placeTrack();
    computeEnd();
    computeProgress();

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', () => { placeTrack(); computeEnd(); onScroll(); }, { passive: true });
    window.addEventListener('load', () => { placeTrack(); computeEnd(); onScroll(); });

    // Observe dynamic layout changes
    if ('ResizeObserver' in window) {
      if (compact) new ResizeObserver(() => placeTrack()).observe(compact);
      if (endEl) new ResizeObserver(() => { computeEnd(); onScroll(); }).observe(endEl);
    }
  });

  /**
   * Initialize when a part of the page was updated
   */
  document.addEventListener('joomla:updated', initTemplate);
})(Joomla, document);
