import Swup from 'https://unpkg.com/swup@3?module';
import SwupFormsPlugin from 'https://unpkg.com/@swup/forms-plugin@2?module';
import SwupBodyClassPlugin from 'https://unpkg.com/@swup/body-class-plugin@2?module';

const swup = new Swup({
  containers: ["#swup"],
  plugins: [new SwupFormsPlugin(), new SwupBodyClassPlugin()]
});

swup.on('pageView', () => {
  const el = document.querySelector('#q');
  if (el) {
    const urlParams = new URLSearchParams(window.location.search);
    const q = urlParams.get('q');
    if (q) {
      el.innerText = q;
    }
  }
});