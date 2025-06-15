function showSection(sectionId) {
  document.querySelectorAll('.profile-section').forEach(div => {
    div.classList.remove('active');
    div.style.display = 'none';
  });
  const section = document.getElementById(sectionId);
  if (section) {
    section.classList.add('active');
    section.style.display = '';
  }
  document.querySelectorAll('.profile-sidebar button').forEach(btn => btn.classList.remove('active'));
  const btn = Array.from(document.querySelectorAll('.profile-sidebar button'))
    .find(b => b.getAttribute('onclick')?.includes(sectionId));
  if (btn) btn.classList.add('active');
}

function showSingleOrder(detailsId) {
  const timestamp = detailsId.replace('order-details-', '');
  const order = window.orderDetailsData.find(o => o.timestamp == timestamp);
  if (!order) return;

  document.querySelectorAll('.profile-section').forEach(div => {
    div.classList.remove('active');
    div.style.display = 'none';
  });
  const detailsSection = document.getElementById('order-details-section');
  detailsSection.style.display = '';
  detailsSection.classList.add('active');

  document.querySelector('.single-order-date').innerHTML = `<b>Order from ${order.dateStr}</b>`;
  document.querySelector('.single-order-id').textContent = `sb-order-${order.orderId}`;
  document.querySelector('.single-order-address').innerHTML = 
      '<b>Shipping Address:</b> ' + (order.address ? order.address : 'N/A');
  document.querySelector('.single-order-images').innerHTML = order.imagesHtml;
  let rows = '';
  order.products.forEach(prod => {
    rows += `<tr>
      <td>${prod.name}</td>
      <td>${prod.amount}</td>
      <td>${Number(prod.price).toFixed(2)} €</td>
    </tr>`;
  });
  document.querySelector('.single-order-products tbody').innerHTML = rows;
  document.querySelector('.single-order-total b').textContent = `Total: ${Number(order.total).toFixed(2)} €`;
}


document.addEventListener('DOMContentLoaded', function() {
  const params = new URLSearchParams(window.location.search);
  const section = params.get('section') || 'orders';
  showSection(section);

  const accountForm = document.querySelector('#sell-account form');
  if (accountForm) {
    accountForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData();
      formData.append('account_game', document.getElementById('account_game').value);
      formData.append('account_description', document.getElementById('account_description').value);
      formData.append('account_price', parseFloat(document.getElementById('account_price').value));
      formData.append('add_account', true);

      const accountPhotos = document.getElementById('account_photos').files;
      for (let i = 0; i < Math.min(3, accountPhotos.length); i++) {
        formData.append('account_photos[]', accountPhotos[i]);
      }

      fetch('add_account.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        const messageDiv = document.createElement('div');
        messageDiv.style.color = data.success ? 'green' : 'red';
        messageDiv.textContent = data.message;
        accountForm.prepend(messageDiv);
        setTimeout(() => messageDiv.remove(), 3000);
        if (data.success) {
          accountForm.reset();
          // Optionally refresh listings or redirect
          window.location.href = 'profile.php?section=listings';
        }
      })
      .catch(error => {
        const messageDiv = document.createElement('div');
        messageDiv.style.color = 'red';
        messageDiv.textContent = 'Error communicating with server.';
        accountForm.prepend(messageDiv);
        setTimeout(() => messageDiv.remove(), 3000);
      });
    });
  }

  const accountPhotosInput = document.getElementById('account_photos');
  if (accountPhotosInput) {
    accountPhotosInput.addEventListener('change', function(e) {
      if (this.files.length > 3) {
        alert('You can upload a maximum of 3 photos.');
        this.value = '';
      }
    });
  }
});
