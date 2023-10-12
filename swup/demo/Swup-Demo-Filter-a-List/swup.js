console.clear();
import Swup from 'https://unpkg.com/swup@4.0.0-rc.26?module';
import FragmentPlugin from 'https://www.unpkg.com/@swup/fragment-plugin@0.1.9?module';

const swup = new Swup({
  containers: ["#swup"],
  plugins: [
    new FragmentPlugin({
      debug: true,
      rules: [
        {
          from: '/items/:filter?',
          to: '/items/:filter?',
          containers: ['#items'],
        }
      ]
    })
  ]
});

function setTransitionDelays() {
  document.querySelectorAll('.list_item').forEach((el, i) => {
    el.style.transitionDelay = i * 20 + 'ms';
  });
}
setTransitionDelays();
swup.hooks.on('page:view', setTransitionDelays)