/* Ceylon Focus News — shared front-end behaviour */

/* Play a homepage/article video inline when its thumbnail is clicked */
function playVideo(btn, url) {
  const frame = btn.parentElement;
  frame.innerHTML = `<iframe src="${url}?autoplay=1" title="video" allow="autoplay; encrypted-media" allowfullscreen></iframe>`;
}

/* Mobile nav toggle */
document.addEventListener('DOMContentLoaded', () => {
  const navToggle = document.getElementById('navToggle');
  const navList = document.getElementById('navList');
  if (navToggle && navList) {
    navToggle.addEventListener('click', () => {
      const open = navList.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', open);
    });
  }
});
