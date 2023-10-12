console.clear();
import Swup from 'https://unpkg.com/swup@4.0.0-rc.26?module';
import FragmentPlugin from 'https://www.unpkg.com/@swup/fragment-plugin@0.1.4?module';

new Swup({
  containers: ["#swup"],
  plugins: [
    new FragmentPlugin({
      debug: true,
      rules: [
        {
          from: '/',
          to: '/detail-(.*)',
          containers: ['#modal'],
          name: 'open-modal'
        },
        {
          from: '/detail-(.*)',
          to: '/',
          containers: ['#modal'],
          name: 'close-modal'
        },
        {
          from: '/detail-(.*)',
          to: '/detail-(.*)',
          containers: ['#detail']
        }
      ]
    })
  ]
});