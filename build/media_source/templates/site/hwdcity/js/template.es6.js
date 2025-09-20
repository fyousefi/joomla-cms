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

    // The section after which we allow hiding:
    const triggerEl = document.querySelector('.container-top-b');

    // Compute the document Y at which hiding is allowed
    let triggerY = 0; // default: top of page (i.e., always allow) when element missing
    function computeTrigger() {
      if (!triggerEl) { triggerY = 0; return; }
      const r = triggerEl.getBoundingClientRect();
      triggerY = window.pageYOffset + r.top; // absolute Y of .container-top-b
    }

    let lastY = window.pageYOffset || 0;
    const threshold = 6; // px to avoid jitter
    const minTop = 32; // always show very near top
    const isMenuOpen = () => !!document.querySelector('.navbar-collapse.show');

    let ticking = false;
    function onScroll() {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => {
        const y = window.pageYOffset || 0;
        const dy = y - lastY;

        // If we're before the trigger section, keep the nav visible.
        const beforeTrigger = y < Math.max(triggerY, minTop);

        if (beforeTrigger || isMenuOpen()) {
          nav.classList.remove('nav-hide');
        } else if (dy > threshold) {
          nav.classList.add('nav-hide'); // scrolling down -> hide
        } else if (dy < -threshold) {
          nav.classList.remove('nav-hide'); // scrolling up -> show
        }

        lastY = y;
        ticking = false;
      });
    }

    // Init + listeners
    computeTrigger();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', () => { computeTrigger(); lastY = window.pageYOffset || 0; });
    window.addEventListener('load', computeTrigger);

    // Optional: keep trigger updated if that section’s height changes
    if ('ResizeObserver' in window && triggerEl) {
      new ResizeObserver(computeTrigger).observe(triggerEl);
    }
  });
  /**
   * Initialize when a part of the page was updated
   */
  document.addEventListener('joomla:updated', initTemplate);
})(Joomla, document);
