document.addEventListener('DOMContentLoaded', function() {
  const gameData = {
    "League of Legends": {
      currency: "Riot Points",
      image: "../data/images/currency/lol_riotpoints.png"
    },
    "Pokemon GO": {
      currency: "PokeCoins",
      image: "../data/images/currency/pokemongo_pokecoins.png"
    },
    "Fortnite": {
      currency: "V-Bucks",
      image: "../data/images/currency/fortnite_vbucks.png"
    },
    "World of Tanks": {
      currency: "Gold",
      image: "../data/images/currency/wot_gold.png"
    },
    "Marvel Rivals": {
      currency: "Lattice",
      image: "../data/images/currency/marvelrivals_lattice.png"
    }
  };

  function showSection(sectionId) {
    document.querySelectorAll('.admin-section').forEach(div => div.classList.remove('active'));
    document.getElementById(sectionId).classList.add('active');
    document.querySelectorAll('.admin-sidebar button').forEach(btn => btn.classList.remove('active'));
    const btn = Array.from(document.querySelectorAll('.admin-sidebar button'))
      .find(b => b.getAttribute('onclick')?.includes(sectionId));
    if (btn) btn.classList.add('active');
  }

  showSection('add-currency');
  window.showSection = showSection;

  const gameSelect = document.getElementById('game');
  if (gameSelect) {
    gameSelect.addEventListener('change', function() {
      const selectedGame = this.value;
      if (gameData[selectedGame]) {
        document.getElementById('currency_name').value = gameData[selectedGame].currency;
        document.getElementById('image_path').value = gameData[selectedGame].image;
        document.getElementById('currency_image_preview').src = gameData[selectedGame].image;
        document.getElementById('currency_image_preview').style.display = 'inline';
      } else {
        document.getElementById('currency_name').value = '';
        document.getElementById('image_path').value = '';
        document.getElementById('currency_image_preview').src = '';
        document.getElementById('currency_image_preview').style.display = 'none';
      }
    });
  }

  const currencyForm = document.querySelector('#add-currency form');
  if (currencyForm) {
    currencyForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = {
        game: document.getElementById('game').value,
        currency_name: document.getElementById('currency_name').value,
        amount: parseInt(document.getElementById('amount').value),
        price: parseFloat(document.getElementById('price').value),
        image_path: document.getElementById('image_path').value,
        add_currency: true
      };

      fetch('../admin/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      })
      .then(res => res.json())
      .then(data => {
        const messageDiv = document.createElement('div');
        messageDiv.style.color = data.success ? 'green' : 'red';
        messageDiv.textContent = data.message;
        currencyForm.prepend(messageDiv);
        setTimeout(() => messageDiv.remove(), 3000);
        if (data.success) {
          currencyForm.reset();
          document.getElementById('currency_image_preview').style.display = 'none';
        }
      })
      .catch(error => {
        const messageDiv = document.createElement('div');
        messageDiv.style.color = 'red';
        messageDiv.textContent = 'Error communicating with server.';
        currencyForm.prepend(messageDiv);
        setTimeout(() => messageDiv.remove(), 3000);
      });
    });
  }

  const accountForm = document.querySelector('#add-account form');
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

      fetch('../admin/admin_panel.php', {
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
  
  document.querySelectorAll('.approve-btn').forEach(button => {
    button.addEventListener('click', function() {
      const accountId = this.getAttribute('data-account-id');
      fetch('../admin/approve_account.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ account_id: accountId, action: 'approve' }),
      })
      .then(res => res.json())
      .then(data => {
        const messageDiv = document.createElement('div');
        messageDiv.style.color = data.success ? 'green' : 'red';
        messageDiv.textContent = data.message;

        document.querySelector('#approve-listings').prepend(messageDiv);
        setTimeout(() => messageDiv.remove(), 3000);
        if (data.success) {
          const row = this.closest('tr');
          if (row) row.remove();
        }
      })
      .catch(error => {
        const messageDiv = document.createElement('div');
        messageDiv.style.color = 'red';
        messageDiv.textContent = 'Error communicating with server.';
        document.querySelector('#approve-listings').prepend(messageDiv);
        setTimeout(() => messageDiv.remove(), 3000);
      });
    });
  });

  document.querySelectorAll('.reject-btn').forEach(button => {
    button.addEventListener('click', function() {
      const row = this.closest('tr');
      const accountId = this.getAttribute('data-account-id');
      fetch('approve_account.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ account_id: accountId, action: 'reject' })
      })
      .then(res => res.json())
      .then(data => {
        const messageDiv = document.createElement('div');
        messageDiv.style.color = data.success ? 'green' : 'red';
        messageDiv.textContent = data.message;
        document.querySelector('#approve-listings').prepend(messageDiv);
        setTimeout(() => messageDiv.remove(), 3000);
        if (data.success) {
          row.remove();
        }
      })
      .catch(error => {
        const messageDiv = document.createElement('div');
        messageDiv.style.color = 'red';
        messageDiv.textContent = 'Error communicating with server.';
        document.querySelector('#approve-listings').prepend(messageDiv);
        setTimeout(() => messageDiv.remove(), 3000);
      });
    });
  });
});


