const currencySymbols = {
  EUR: '€',
  USD: '$',
  KRW: '₩',
  JPY: '¥',
  CHF: 'Fr',
  GBP: '£',
};

const categoryTitles = {
  all: "Game Currency",
  league_of_legends: "Riot Points",
  world_of_tanks: "Gold",
  fortnite: "V-Bucks",
  pokemon_go: "PokeCoints",
  marvel_rivals: "Lattice"
};

const USE_EXCHANGE_API = true;

function updatePrices(selectedCurrency) {
  if (selectedCurrency === 'EUR' || !USE_EXCHANGE_API) {
    document.querySelectorAll('.currency-price').forEach(el => {
      const basePrice = parseFloat(el.getAttribute('data-base-price'));
      el.textContent = basePrice.toFixed(2) + ' €';
    });
    if (selectedCurrency !== 'EUR' && !USE_EXCHANGE_API) {
      alert('Currency conversion is disabled in development mode.');
      document.getElementById('currency-select').value = 'EUR';
      localStorage.setItem('preferredCurrency', 'EUR');
    }
    return;
  }

  fetch(`https://api.exchangeratesapi.io/v1/latest?access_key=${EXCHANGE_API_KEY}&format=1`)
    .then(res => {
      if (!res.ok) throw new Error('API request failed');
      return res.json();
    })
    .then(data => {
      const rate = data.rates[selectedCurrency];
      if (!rate) throw new Error('Invalid exchange rate');
      document.querySelectorAll('.currency-price').forEach(el => {
        const basePrice = parseFloat(el.getAttribute('data-base-price'));
        el.textContent = (basePrice * rate).toFixed(2) + ' ' + currencySymbols[selectedCurrency];
      });
    })
    .catch(error => {
      console.error('Error fetching exchange rate:', error);
      document.querySelectorAll('.currency-price').forEach(el => {
        const basePrice = parseFloat(el.getAttribute('data-base-price'));
        el.textContent = basePrice.toFixed(2) + ' €';
      });
      alert('Failed to fetch exchange rate. Displaying prices in EUR.');
      document.getElementById('currency-select').value = 'EUR';
      localStorage.setItem('preferredCurrency', 'EUR');
    });
}

document.addEventListener('DOMContentLoaded', () => {
  const preferredCurrency = localStorage.getItem('preferredCurrency') || 'EUR';
  document.getElementById('currency-select').value = preferredCurrency;
  updatePrices(preferredCurrency);

  document.querySelectorAll('.currency-category').forEach(btn => {
    btn.addEventListener('click', () => {
      const game = btn.getAttribute('data-game');
      filterCurrency(game);
    });
  });
});

document.getElementById('currency-select').addEventListener('change', function() {
  const selectedCurrency = this.value;
  localStorage.setItem('preferredCurrency', selectedCurrency);
  updatePrices(selectedCurrency);
});

function filterCurrency(game) {
  document.querySelectorAll('.currency-category').forEach(btn => btn.classList.remove('active'));
  document.querySelector(`.currency-category[data-game="${game}"]`).classList.add('active');

  const header = document.getElementById('currency-header-title');
  header.textContent = categoryTitles[game] || "Game Currency";

  document.querySelectorAll('.currency-card').forEach(card => {
    if (game === 'all') {
      card.style.display = '';
    } else {
      card.style.display = card.getAttribute('data-category') === game ? '' : 'none';
    }
  });
}

document.getElementById('sort-select').addEventListener('change', function() {
  sortCurrencyCards(this.value);
});

function sortCurrencyCards(sortType) {
  const list = document.querySelector('.currency-list');
  const cards = Array.from(list.querySelectorAll('.currency-card'))
    .filter(card => card.style.display !== 'none'); 

  cards.sort((a, b) => {
    if (sortType === 'price_asc') {
      return parseFloat(a.querySelector('.currency-price').getAttribute('data-base-price')) -
             parseFloat(b.querySelector('.currency-price').getAttribute('data-base-price'));
    }
    if (sortType === 'price_desc') {
      return parseFloat(b.querySelector('.currency-price').getAttribute('data-base-price')) -
             parseFloat(a.querySelector('.currency-price').getAttribute('data-base-price'));
    }
  });

  cards.forEach(card => list.appendChild(card));
}