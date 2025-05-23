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

document.addEventListener('DOMContentLoaded', function() {
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
});
document.getElementById('account_photos').addEventListener('change', function(e) {
  if (this.files.length > 3) {
    alert('You can upload a maximum of 3 photos.');
    this.value = '';
  }
});