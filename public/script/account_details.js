let idx = 0;
function showImg(i) {
  idx = (i + photos.length) % photos.length;
  document.getElementById('carousel-img').src = photos[idx];
}
function prevImg() { showImg(idx - 1); }
function nextImg() { showImg(idx + 1); }

document.addEventListener('DOMContentLoaded', function() {
  const carouselImg = document.getElementById('carousel-img');
  const lightboxOverlay = document.getElementById('lightbox-overlay');
  const lightboxImg = document.getElementById('lightbox-img');
  const lightboxPrev = document.getElementById('lightbox-prev');
  const lightboxNext = document.getElementById('lightbox-next');

  if (carouselImg && lightboxOverlay && lightboxImg) {
    carouselImg.style.cursor = 'zoom-in';
    carouselImg.addEventListener('click', function() {
      lightboxImg.src = this.src;
      lightboxOverlay.classList.add('active');
    });

    lightboxOverlay.addEventListener('click', function(e) {
      if (e.target === lightboxOverlay) {
        lightboxOverlay.classList.remove('active');
        lightboxImg.src = '';
      }
    });

    if (lightboxPrev && lightboxNext) {
      lightboxPrev.addEventListener('click', function(e) {
        e.stopPropagation();
        idx = (idx - 1 + photos.length) % photos.length;
        lightboxImg.src = photos[idx];
      });
      lightboxNext.addEventListener('click', function(e) {
        e.stopPropagation();
        idx = (idx + 1) % photos.length;
        lightboxImg.src = photos[idx];
      });
    }

    carouselImg.addEventListener('click', function() {
      idx = photos.indexOf(this.src);
      if (idx === -1) idx = 0;
      lightboxImg.src = photos[idx];
      lightboxOverlay.classList.add('active');
    });
  }
});
