import Swup from 'https://unpkg.com/swup@4.0.0-rc.21?module';
import SwupParallelPlugin from 'https://unpkg.com/@swup/parallel-plugin@0.0.2?module';

const swup = new Swup({
  containers: ["#swup"],
  plugins: [new SwupParallelPlugin()]
});
