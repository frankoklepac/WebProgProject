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