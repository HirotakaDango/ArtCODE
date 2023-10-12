
import Swup from 'https://unpkg.com/swup@4?module';
import SwupScrollPlugin from 'https://unpkg.com/@swup/scroll-plugin?module';
import SwupPreloadPlugin from 'https://unpkg.com/@swup/preload-plugin?module';
console.log(SwupPreloadPlugin);

const nextLinkSelector = '.pagination__next';
const listSelector = '.list';
const listItemSelector = '.list_item';
const buttonSelector = '.load-more';

const swup = new Swup({
  plugins: [
    new SwupPreloadPlugin(), // will put the first page into the cache automatically
    new SwupScrollPlugin({
      animateScroll: false,
      shouldResetScrollPosition: (link) => !link.matches('.backlink')
    })
  ]
});

/**
 * Injects new items loaded by infinite-scroll into swup's cache entry of the current page
 */
function updateCache(containerSelector, items, nextLink) {
  const url = swup.getCurrentUrl();
  const cachedPage = swup.cache.get(url);
  if (!cachedPage) return;

  const cachedDocument = new DOMParser().parseFromString(cachedPage.html, 'text/html')
  const container = cachedDocument.querySelector(containerSelector);
  if (!container) return;

  // Update the items
  const clonedItems = [...items].map(item => item.cloneNode(true));
  container.append(...clonedItems);

  // Update the next link
  if (nextLink) {
    cachedDocument.querySelector(nextLinkSelector)?.replaceWith(nextLink.cloneNode(true));
  }

  // Save the modified html as a string in the cache entry
  cachedPage.html = cachedDocument.documentElement.outerHTML;
  swup.cache.update(url, cachedPage);
}

/**
 * Initialize infinite scroll (executed on selected swup hooks further down)
 */
function initInfiniteScroll() {
  const el = document.querySelector(listSelector);
  if (!el) return;

  const nextLink = document.querySelector(nextLinkSelector);
  if (!nextLink) return;

  const infScroll = new InfiniteScroll(el, {
    path: nextLinkSelector,
    append: listItemSelector,
    history: false,
    prefill: false,
    button: buttonSelector,
    scrollThreshold: false,
  });

  infScroll.on("append", (doc, _path, items) => {
    // Get the next link. If there is no more available, replace it with an empty <span>
    const nextLink = doc.querySelector(nextLinkSelector) ?? doc.createElement('span');
    document.querySelector(nextLinkSelector)?.replaceWith(nextLink);
    // Update the cache
    updateCache(listSelector, items, nextLink);
    items.forEach(item => item.classList.add('is-new'));
    nextTick(() => {
      items.forEach(item => item.classList.remove('is-new'));
    })
  });
}

/**
 * When we visit the page with the infinite scroll directly, that page's cache isn't there immediately. 
 * Preload Plugin takes care of (re-)preloading a fresh copy from the server and putting the result in swup's cache.
 * By hooking *once* into "page:preload", we can initialize our infinite scroll after the cached version of the page has arrived, so 
 * that we can safely append new items as they arrive.
 */
swup.hooks.once("page:preload", initInfiniteScroll)
/** Also init infinite scroll on every page view */
swup.hooks.on("page:view", initInfiniteScroll);

/** 
 * Helper function to wait for the next tick
 */
function nextTick(cb) {
  requestAnimationFrame(() => {
    requestAnimationFrame(cb)
  });
}