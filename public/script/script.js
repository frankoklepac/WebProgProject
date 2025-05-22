window.addEventListener('scroll', function() {
  const navbar = document.querySelector('.navbar');
  if (window.scrollY > 0) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
});

document.addEventListener('DOMContentLoaded', function() {
  const icon = document.getElementById('profileIcon');
  const dropdown = document.getElementById('profileDropdown');

  icon.addEventListener('click', function(e) {
    dropdown.classList.toggle('active');
    e.stopPropagation();
  });

  document.addEventListener('click', function(e) {
    if (!icon.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove('active');
    }
  });

  dropdown.addEventListener('mouseenter', function() {
    dropdown.classList.add('active');
  });

  dropdown.addEventListener('mouseleave', function() {
    if (!dropdown.classList.contains('active')) {
      dropdown.classList.remove('active');
    }
  });
});

document.addEventListener('DOMContentLoaded', function() {
  const hamburger = document.getElementById('hamburger');
  const navCenter = document.querySelector('.nav-center');
  hamburger.addEventListener('click', function() {
    navCenter.classList.toggle('active');
  });
});