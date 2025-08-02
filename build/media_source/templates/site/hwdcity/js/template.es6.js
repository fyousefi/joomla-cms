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

  /**
   * Initialize when a part of the page was updated
   */
  document.addEventListener('joomla:updated', initTemplate);
})(Joomla, document);
