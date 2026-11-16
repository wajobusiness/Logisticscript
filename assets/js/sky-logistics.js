/**
 * Sky Logistics Interactive Component Controller
 * Recreating modern smooth UX for header, mobile menu, accordion, stats, and scroll
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Sticky Header
  const header = document.querySelector('.site-header');
  if (header) {
    const handleScroll = () => {
      if (window.scrollY > 80) {
        header.classList.add('sticky-header');
      } else {
        header.classList.remove('sticky-header');
      }
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  // 2. Mobile Drawer Navigation
  const mobileToggle = document.querySelector('.mobile-nav-toggle');
  const mobileDrawer = document.querySelector('.mobile-drawer');
  const mobileOverlay = document.querySelector('.mobile-drawer-overlay');
  const mobileClose = document.querySelector('.mobile-drawer-close');

  const openDrawer = () => {
    if (mobileDrawer && mobileOverlay) {
      mobileDrawer.classList.add('active');
      mobileOverlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  };

  const closeDrawer = () => {
    if (mobileDrawer && mobileOverlay) {
      mobileDrawer.classList.remove('active');
      mobileOverlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  };

  if (mobileToggle) mobileToggle.addEventListener('click', openDrawer);
  if (mobileClose) mobileClose.addEventListener('click', closeDrawer);
  if (mobileOverlay) mobileOverlay.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDrawer();
  });

  // 3. FAQ Accordion
  const accordionItems = document.querySelectorAll('.faq-accordion-item');
  accordionItems.forEach((item) => {
    const questionBtn = item.querySelector('.faq-question-btn');
    if (questionBtn) {
      questionBtn.addEventListener('click', () => {
        const isActive = item.classList.contains('active');
        // Close other items
        accordionItems.forEach((other) => other.classList.remove('active'));
        if (!isActive) {
          item.classList.add('active');
        }
      });
    }
  });

  // 4. Scroll to Top
  const scrollTopBtn = document.querySelector('.scroll-to-top');
  if (scrollTopBtn) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 350) {
        scrollTopBtn.classList.add('active');
      } else {
        scrollTopBtn.classList.remove('active');
      }
    }, { passive: true });

    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // 5. Stat Counter Animation on Viewport
  const counters = document.querySelectorAll('.stat-number');
  if (counters.length > 0 && 'IntersectionObserver' in window) {
    const counterObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const targetText = el.getAttribute('data-target') || el.innerText;
          // Simple count animation if numeric
          const numericVal = parseFloat(targetText.replace(/[^0-9.]/g, ''));
          if (!isNaN(numericVal) && !el.dataset.animated) {
            el.dataset.animated = 'true';
            let start = 0;
            const duration = 1500;
            const startTime = performance.now();

            const updateCounter = (currentTime) => {
              const elapsed = currentTime - startTime;
              const progress = Math.min(elapsed / duration, 1);
              const current = Math.floor(progress * numericVal);
              
              // Re-attach prefix / suffix
              let formatted = current.toLocaleString();
              if (targetText.includes('+')) formatted += '+';
              if (targetText.includes('K')) formatted += 'K';
              if (targetText.includes('M')) formatted += 'M';
              el.innerText = formatted;

              if (progress < 1) {
                requestAnimationFrame(updateCounter);
              } else {
                el.innerText = targetText;
              }
            };
            requestAnimationFrame(updateCounter);
          }
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.3 });

    counters.forEach((c) => counterObserver.observe(c));
  }
});
