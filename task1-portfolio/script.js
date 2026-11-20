// Portfolio Website - Interactive Scripts

document.addEventListener('DOMContentLoaded', function() {

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  // Navbar background on scroll
  const header = document.querySelector('header');
  let lastScroll = 0;

  window.addEventListener('scroll', function() {
    const currentScroll = window.pageYOffset;

    if (currentScroll > lastScroll && currentScroll > 100) {
      header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    } else {
      header.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
    }

    lastScroll = currentScroll;
  });

  // Scroll reveal animation
  const observerOptions = {
    threshold: 0.1,
    margin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, observerOptions);

  // Observe all sections
  document.querySelectorAll('section').forEach(section => {
    section.style.opacity = '0';
    section.style.transform = 'translateY(20px)';
    section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(section);
  });

  // Mobile menu toggle (for future expansion)
  const navLinks = document.querySelector('.nav-links');
  const menuBtn = document.createElement('button');
  menuBtn.className = 'menu-btn';
  menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
  menuBtn.style.display = 'none';
  menuBtn.style.fontSize = '1.5rem';
  menuBtn.style.cursor = 'pointer';

  const headerRight = document.querySelector('.logo');
  headerRight.parentNode.insertBefore(menuBtn, headerRight.nextSibling);

  menuBtn.addEventListener('click', function() {
    navLinks.classList.toggle('active');
  });

  // Form submission feedback
  const contactForm = document.querySelector('.contact-form form');
  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      const btn = this.querySelector('button[type="submit"]');
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-check"></i> Sent!';
      btn.style.background = '#28a745';

      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.style.background = '';
      }, 3000);
    });
  }

  // Skill bar animation on scroll
  const skillBars = document.querySelectorAll('.skill-box');
  const skillObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.width = entry.target.getAttribute('data-width');
      }
    });
  }, { threshold: 0.5 });

  skillBars.forEach(bar => {
    skillObserver.observe(bar);
  });
});