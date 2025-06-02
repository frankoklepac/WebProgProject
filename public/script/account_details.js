let idx = 0;
function showImg(i) {
  idx = (i + photos.length) % photos.length;
  document.getElementById('carousel-img').src = photos[idx];
}
function prevImg() { showImg(idx - 1); }
function nextImg() { showImg(idx + 1); }