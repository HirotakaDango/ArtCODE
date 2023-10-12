import Swup from 'https://unpkg.com/swup@4?module';
import SwupFormsPlugin from 'https://unpkg.com/@swup/forms-plugin@3?module';

const swup = new Swup({
  containers: ["#swup"],
  plugins: [new SwupFormsPlugin()]
});
