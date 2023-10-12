import Swup from 'https://unpkg.com/swup@4.0.0-rc.21?module';
import SwupParallelPlugin from 'https://unpkg.com/@swup/parallel-plugin@0.0.2?module';

const swup = new Swup({
  containers: ["#swup"],
  plugins: [new SwupParallelPlugin()]
});

swup.hooks.on('visit:start', (context) => {
  let x = 0.5;
  let y = 0.5;
  const event = context.trigger.event;
  if (event && typeof event.clientX === 'number') {
    x = event.clientX / window.innerWidth;
    y = event.clientY / window.innerHeight;
  }
  document.documentElement.style.setProperty('--click-x', x);
  document.documentElement.style.setProperty('--click-y', y);
});
