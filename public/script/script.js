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

document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const productId = this.getAttribute('data-product-id');
    const productType = this.getAttribute('data-product-type');
    fetch('add_to_cart.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'product_id=' + encodeURIComponent(productId) + '&product_type=' + encodeURIComponent(productType)
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
          cartCount.textContent = data.cart_count;
        } else {
          const cartIcon = document.querySelector('.cart-icon');
          if (cartIcon) {
            const span = document.createElement('span');
            span.className = 'cart-count';
            span.textContent = data.cart_count;
            cartIcon.parentNode.appendChild(span);
          }
        }
      } else {
        alert(data.message || 'Could not add to cart.');
      }
    })
    .catch(() => alert('Could not add to cart. Please try again.'));
  });
});