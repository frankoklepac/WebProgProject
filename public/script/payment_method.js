const onlineRadio = document.getElementById('online');
const codRadio = document.getElementById('cod');
const ccFields = document.getElementById('credit-card-fields');
const ccNumber = document.getElementById('cc_number');
const ccExpiry = document.getElementById('cc_expiry');
const ccCvc = document.getElementById('cc_cvc');

function updateCCFields() {
  if (onlineRadio.checked) {
    ccFields.style.display = '';
    ccNumber.required = true;
    ccExpiry.required = true;
    ccCvc.required = true;
  } else {
    ccFields.style.display = 'none';
    ccNumber.required = false;
    ccExpiry.required = false;
    ccCvc.required = false;
  }
}

codRadio.addEventListener('change', updateCCFields);
onlineRadio.addEventListener('change', updateCCFields);

updateCCFields();

document.getElementById('paymentForm').addEventListener('submit', function(e) {
  if (onlineRadio.checked) {
    const ccNumber = document.getElementById('cc_number').value.replace(/\s+/g, '');
    if (!/^\d{16}$/.test(ccNumber)) {
      alert('Please enter a valid 16-digit credit card number.');
      document.getElementById('cc_number').focus();
      e.preventDefault();
      return false;
    }
    const ccExpiry = document.getElementById('cc_expiry').value;
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(ccExpiry)) {
      alert('Please enter a valid expiry date in MM/YY format.');
      document.getElementById('cc_expiry').focus();
      e.preventDefault();
      return false;
    }

    const [month, year] = ccExpiry.split('/');
    const currentYear = new Date().getFullYear() % 100; 
    const currentMonth = new Date().getMonth() + 1;
    
    if (parseInt(month, 10) < 1 || parseInt(month, 10) > 12) {
      alert('Please enter a valid month (01-12).');
      document.getElementById('cc_expiry').focus();
      e.preventDefault();
      return false;
    }

    if ((parseInt(year,10) < parseInt(currentYear, 10)) || 
        (parseInt(year, 10) === parseInt(currentYear, 10) && parseInt(month, 10) < currentMonth)) {
      alert('Card expired');
      document.getElementById('cc_expiry').focus();
      e.preventDefault();
      return false;
    }
    const ccCvc = document.getElementById('cc_cvc').value;
    if (!/^\d{3,4}$/.test(ccCvc)) {
      alert('Please enter a valid 3 or 4 digit CVC.');
      document.getElementById('cc_cvc').focus();
      e.preventDefault();
      return false;
    }
  }
});

document.getElementById('cc_number').addEventListener('input', function(e) {
  let value = this.value.replace(/\D/g, '');
  value = value.substring(0, 16);
  let formatted = value.replace(/(.{4})/g, '$1 ').trim();
  this.value = formatted;
});

document.getElementById('cc_expiry').addEventListener('input', function(e) {
  let value = this.value.replace(/\D/g, '');
  value = value.substring(0, 4);
  if (value.length > 2) {
    value = value.substring(0,2) + '/' + value.substring(2);
  }
  this.value = value;
});