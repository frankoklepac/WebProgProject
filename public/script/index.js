document.addEventListener('DOMContentLoaded', function() {
  const animatedEls = document.querySelectorAll('.animate-fly-up, .animate-fly-left, .animate-fly-right');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target); 
      }
    });
  }, { threshold: 0.2 });

  animatedEls.forEach(el => observer.observe(el));
});