document.addEventListener('DOMContentLoaded', function() {
  const icon = document.getElementById('profileIcon');
  const dropdown = document.getElementById('profileDropdown');
  icon.addEventListener('click', function(e) {
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    e.stopPropagation();
  });
  document.addEventListener('click', function() {
    dropdown.style.display = 'none';
  });
});

document.addEventListener('DOMContentLoaded', function() {
  const hamburger = document.getElementById('hamburger');
  const navCenter = document.querySelector('.nav-center');
  hamburger.addEventListener('click', function() {
    navCenter.classList.toggle('active');
  });
});