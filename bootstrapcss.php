    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    </noscript>
    <meta name="view-transition" content="same-origin">

    <style>
      @view-transition {
        navigation: auto;
      }

      :root {
        view-transition-name: root;
      }

      ::view-transition-old(root) {
        animation: 1s fade-out 0s ease;
      }

      ::view-transition-new(root) {
        animation: 1s fade-in 0s ease;
      }

      @keyframes fade-out {
        from {
          opacity: 1;
        }
        to {
          opacity: 0;
        }
      }

      @keyframes fade-in {
        from {
          opacity: 0;
        }
        to {
          opacity: 1;
        }
      }

      /* For Webkit-based browsers */
      ::-webkit-scrollbar {
        width: 0;
        height: 0;
        border-radius: 10px;
      }

      ::-webkit-scrollbar-track {
        border-radius: 0;
      }

      ::-webkit-scrollbar-thumb {
        border-radius: 0;
      }

      .text-stroke {
        -webkit-text-stroke: 1px;
      }
    </style>

    <script defer>
      (() => {
        // Configuration constants
        const CONFIG = {
          IMAGE_STORE_KEY: 'imageCacheStore-v2',
          DB_NAME: 'imageCacheDB-v2',
          STORE_NAME: 'images',
          CACHE_NAME: 'ArtCODE-v2.1.0-ultra',
          VERSION: 3,
          RETRY_LIMIT: 3,
          RETRY_DELAY: 500,
          BATCH_SIZE: 12,
          OBSERVER_THRESHOLD: 0.1
        };
        
        // IndexedDB connection
        let db;
        
        // Performance metrics
        const metrics = {
          storageAPICacheHits: 0,
          indexedDBCacheHits: 0,
          networkFetches: 0,
          cacheErrors: 0,
          totalImages: 0
        };
        
        // Storage API Manager
        const StorageAPI = {
          async isAvailable() {
            return 'storage' in navigator && 'getDirectory' in navigator.storage;
          },
          
          async getDirectory() {
            if (!await this.isAvailable()) return null;
            try {
              const root = await navigator.storage.getDirectory();
              return await root.getDirectoryHandle(CONFIG.IMAGE_STORE_KEY, { create: true });
            } catch (e) {
              console.warn('[StorageAPI] Error getting directory:', e);
              return null;
            }
          },
          
          async store(url, blob) {
            try {
              const dir = await this.getDirectory();
              if (!dir) return false;
              
              const fileName = encodeURIComponent(url);
              const fileHandle = await dir.getFileHandle(fileName, { create: true });
              const writable = await fileHandle.createWritable();
              await writable.write(blob);
              await writable.close();
              return true;
            } catch (e) {
              return false;
            }
          },
          
          async get(url) {
            try {
              const dir = await this.getDirectory();
              if (!dir) return null;
              
              const fileName = encodeURIComponent(url);
              const fileHandle = await dir.getFileHandle(fileName);
              return await fileHandle.getFile();
            } catch (e) {
              return null;
            }
          }
        };
        
        // IndexedDB Manager
        const IndexedDB = {
          async init() {
            if (db) return db;
            
            return new Promise((resolve, reject) => {
              const req = indexedDB.open(CONFIG.DB_NAME, CONFIG.VERSION);
              
              req.onupgradeneeded = e => {
                const _db = e.target.result;
                if (!_db.objectStoreNames.contains(CONFIG.STORE_NAME)) {
                  _db.createObjectStore(CONFIG.STORE_NAME);
                }
              };
              
              req.onsuccess = e => {
                db = e.target.result;
                resolve(db);
              };
              
              req.onerror = e => {
                console.error('[IndexedDB] Error opening database:', e.target.error);
                reject(e.target.error);
              };
            });
          },
          
          async store(url, blob) {
            try {
              if (!db) await this.init();
              
              return new Promise((resolve, reject) => {
                const tx = db.transaction([CONFIG.STORE_NAME], 'readwrite');
                tx.objectStore(CONFIG.STORE_NAME).put(blob, url);
                
                tx.oncomplete = () => resolve(true);
                tx.onerror = e => {
                  console.warn('[IndexedDB] Error storing image:', e);
                  reject(e);
                };
              });
            } catch (e) {
              return false;
            }
          },
          
          async get(url) {
            try {
              if (!db) await this.init();
              
              return new Promise((resolve, reject) => {
                const tx = db.transaction([CONFIG.STORE_NAME], 'readonly');
                const req = tx.objectStore(CONFIG.STORE_NAME).get(url);
                
                req.onsuccess = () => resolve(req.result || null);
                req.onerror = e => {
                  console.warn('[IndexedDB] Error getting image:', e);
                  reject(e);
                };
              });
            } catch (e) {
              return null;
            }
          }
        };
        
        // Image processing utilities
        const ImageUtils = {
          createBlobURL(blob, origUrl) {
            return URL.createObjectURL(blob);
          },
          
          setElementSrc(elem, blob, origUrl) {
            if (!elem) return;
            
            const blobUrl = this.createBlobURL(blob, origUrl);
            
            // Update main src if not already a blob URL
            if (!elem.src?.startsWith('blob:') || elem.getAttribute('src') === origUrl) {
              elem.src = blobUrl;
            }
            
            // Update data-src if present
            if (elem.dataset?.src && elem.dataset.src !== blobUrl && 
                (!elem.dataset.src.startsWith('blob:') || elem.dataset.src === origUrl)) {
              elem.dataset.src = blobUrl;
            }
            
            // Trigger load event if image hadn't loaded yet
            if (!elem.complete) {
              const onLoad = () => {
                elem.classList.add('cache-loaded');
                elem.removeEventListener('load', onLoad);
              };
              elem.addEventListener('load', onLoad);
            } else {
              elem.classList.add('cache-loaded');
            }
          },
          
          async fetchWithRetry(url, retries = CONFIG.RETRY_LIMIT) {
            metrics.networkFetches++;
            try {
              const resp = await fetch(url, { cache: 'force-cache' });
              if (!resp.ok) throw new Error(`HTTP error ${resp.status}`);
              return await resp.blob();
            } catch (err) {
              if (retries > 0) {
                await new Promise(r => setTimeout(r, CONFIG.RETRY_DELAY));
                return this.fetchWithRetry(url, retries - 1);
              }
              metrics.cacheErrors++;
              throw err;
            }
          }
        };
        
        // Main Cache Manager
        const CacheManager = {
          async cacheImage(url, imgElem) {
            if (!url || url.startsWith('blob:') || url.startsWith('data:')) return null;
            metrics.totalImages++;
            
            // Try Storage API first
            const storageFile = await StorageAPI.get(url);
            if (storageFile) {
              metrics.storageAPICacheHits++;
              if (imgElem) ImageUtils.setElementSrc(imgElem, storageFile, url);
              return storageFile;
            }
            
            // Try IndexedDB second
            const indexedBlob = await IndexedDB.get(url);
            if (indexedBlob) {
              metrics.indexedDBCacheHits++;
              if (imgElem) ImageUtils.setElementSrc(imgElem, indexedBlob, url);
              // Also store in Storage API for future faster access
              StorageAPI.store(url, indexedBlob).catch(() => {});
              return indexedBlob;
            }
            
            // Fetch from network if not in cache
            try {
              const blob = await ImageUtils.fetchWithRetry(url);
              if (imgElem) ImageUtils.setElementSrc(imgElem, blob, url);
              
              // Store in both caches asynchronously
              Promise.all([
                StorageAPI.store(url, blob),
                IndexedDB.store(url, blob)
              ]).catch(() => {});
              
              return blob;
            } catch (err) {
              console.warn(`[Cache] Failed to fetch image: ${url}`, err);
              return null;
            }
          },
          
          async cacheAllImages(container = document) {
            const urls = new Set();
            const imgElems = new Map();
            
            // Collect all image URLs (both src and data-src)
            container.querySelectorAll('img').forEach(img => {
              const sources = [
                img.getAttribute('src'),
                img.getAttribute('data-src')
              ].filter(Boolean);
              
              sources.forEach(url => {
                if (url && !url.startsWith('blob:') && !url.startsWith('data:')) {
                  urls.add(url);
                  if (!imgElems.has(url)) imgElems.set(url, []);
                  imgElems.get(url).push(img);
                }
              });
            });
            
            // Process in batches to avoid overwhelming the browser
            const urlArray = Array.from(urls);
            for (let i = 0; i < urlArray.length; i += CONFIG.BATCH_SIZE) {
              const batch = urlArray.slice(i, i + CONFIG.BATCH_SIZE);
              await Promise.allSettled(batch.map(url => 
                this.cacheImage(url, imgElems.get(url)[0])
                  .then(blob => {
                    // Update all elements with this URL
                    if (blob) imgElems.get(url).forEach(img => 
                      ImageUtils.setElementSrc(img, blob, url)
                    );
                  })
              ));
            }
            
            return metrics;
          },
          
          getMetrics() {
            const hitRate = metrics.totalImages ? 
              ((metrics.storageAPICacheHits + metrics.indexedDBCacheHits) / metrics.totalImages * 100).toFixed(1) : 
              0;
            
            return {
              ...metrics,
              hitRate: `${hitRate}%`,
              storageAPI: `${metrics.storageAPICacheHits} hits`,
              indexedDB: `${metrics.indexedDBCacheHits} hits`,
              network: `${metrics.networkFetches} fetches`,
              errors: metrics.cacheErrors
            };
          }
        };
        
        // Intersection Observer for lazy loading
        function setupLazyLoading() {
          if (!('IntersectionObserver' in window)) return;
          
          const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                const img = entry.target;
                const dataSrc = img.dataset.src;
                
                if (dataSrc) {
                  CacheManager.cacheImage(dataSrc, img);
                  observer.unobserve(img);
                }
              }
            });
          }, { threshold: CONFIG.OBSERVER_THRESHOLD });
          
          document.querySelectorAll('img[data-src]').forEach(img => {
            if (!img.src || img.src === window.location.href) {
              // Set placeholder or low-quality image if not already set
              if (!img.src) img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"%3E%3C/svg%3E';
              observer.observe(img);
            }
          });
        }
        
        // Service Worker registration and management
        function registerServiceWorker(precacheFiles = [], dynamicPatterns = []) {
          if (!('serviceWorker' in navigator)) return;
          
          // Generate SW code with dynamic configuration
          const swCode = `
            const CACHE_NAME = '${CONFIG.CACHE_NAME}';
            const IMAGE_STORE_KEY = '${CONFIG.IMAGE_STORE_KEY}';
            const PRECACHE_URLS = ${JSON.stringify(precacheFiles)};
            const DYNAMIC_PATTERNS = ${JSON.stringify(dynamicPatterns)};
            
            // Install event - precache core assets
            self.addEventListener('install', event => {
              event.waitUntil(
                caches.open(CACHE_NAME)
                  .then(cache => cache.addAll(PRECACHE_URLS))
                  .then(() => self.skipWaiting())
              );
            });
            
            // Activate event - clean old caches and claim clients
            self.addEventListener('activate', event => {
              event.waitUntil(
                caches.keys().then(keys => Promise.all(
                  keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
                )).then(() => self.clients.claim())
              );
            });
            
            // Helper to check if URL matches dynamic patterns
            function matchesDynamicPattern(url) {
              return DYNAMIC_PATTERNS.some(pattern => new RegExp(pattern).test(url));
            }
            
            // Offline fallback HTML
            const OFFLINE_HTML = '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Offline</title><style>body{font-family:system-ui,sans-serif;max-width:600px;margin:0 auto;padding:20px}h1{color:#333}</style></head><body><h1>You are offline</h1><p>The content you requested is not available while offline.</p></body></html>';
            
            // Fetch event - complex caching strategy
            self.addEventListener('fetch', event => {
              const url = event.request.url;
              
              // Skip non-GET requests
              if (event.request.method !== 'GET') return;
              
              // Images: Storage API first (when available), then cache, then network
              if (event.request.destination === 'image') {
                event.respondWith((async () => {
                  // Try getting from Storage API first (fastest)
                  if ('storage' in self && 'getDirectory' in self.storage) {
                    try {
                      const root = await self.storage.getDirectory();
                      const imagesDir = await root.getDirectoryHandle(IMAGE_STORE_KEY, { create: false });
                      const fileName = encodeURIComponent(url);
                      const fileHandle = await imagesDir.getFileHandle(fileName);
                      const file = await fileHandle.getFile();
                      return new Response(file, { headers: { 'Content-Type': file.type } });
                    } catch (e) {
                      // Silently fail and continue to next strategy
                    }
                  }
                  
                  // Try cache
                  const cache = await caches.open(CACHE_NAME);
                  const cachedResponse = await cache.match(event.request);
                  if (cachedResponse) return cachedResponse;
                  
                  // Network fetch with cache update
                  try {
                    const networkResp = await fetch(event.request);
                    if (networkResp && networkResp.status === 200) {
                      cache.put(event.request, networkResp.clone());
                    }
                    return networkResp;
                  } catch (e) {
                    return new Response('', { status: 404 });
                  }
                })());
                return;
              }
              
              // API endpoints: Network-first strategy
              if (matchesDynamicPattern(url)) {
                event.respondWith(
                  fetch(event.request)
                    .then(response => {
                      const respClone = response.clone();
                      caches.open(CACHE_NAME)
                        .then(cache => cache.put(event.request, respClone));
                      return response;
                    })
                    .catch(() => caches.match(event.request))
                );
                return;
              }
              
              // HTML navigation: Network with offline fallback
              if (event.request.mode === 'navigate') {
                event.respondWith(
                  fetch(event.request)
                    .then(response => {
                      const respClone = response.clone();
                      caches.open(CACHE_NAME)
                        .then(cache => cache.put(event.request, respClone));
                      return response;
                    })
                    .catch(() => 
                      caches.match(event.request)
                        .then(cachedResp => cachedResp || 
                          new Response(OFFLINE_HTML, { 
                            headers: { 'Content-Type': 'text/html' },
                            status: 200,
                            statusText: 'OK'
                          })
                        )
                    )
                );
                return;
              }
              
              // Everything else: Stale-while-revalidate
              event.respondWith(
                caches.match(event.request)
                  .then(cachedResp => {
                    const fetchPromise = fetch(event.request)
                      .then(networkResp => {
                        if (networkResp && networkResp.status === 200) {
                          const clone = networkResp.clone();
                          caches.open(CACHE_NAME)
                            .then(cache => cache.put(event.request, clone));
                        }
                        return networkResp;
                      })
                      .catch(() => cachedResp);
                      
                    return cachedResp || fetchPromise;
                  })
              );
            });
          `;
          
          // Register the service worker from blob
          const swBlob = new Blob([swCode], { type: 'application/javascript' });
          navigator.serviceWorker.register(URL.createObjectURL(swBlob))
            .then(reg => {
              console.info('[ServiceWorker] Registered successfully', reg.scope);
              // Force update if needed
              if (reg.active) reg.update();
            })
            .catch(err => console.error('[ServiceWorker] Registration failed:', err));
        }
        
        // PWA installation prompt handler
        function setupInstallPrompt() {
          let deferredPrompt;
          
          window.addEventListener('beforeinstallprompt', e => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show install button if exists
            const installBtn = document.getElementById('installButton');
            if (installBtn) {
              installBtn.style.display = 'block';
              installBtn.addEventListener('click', () => {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(result => {
                  if (result.outcome === 'accepted') {
                    installBtn.style.display = 'none';
                  }
                  deferredPrompt = null;
                });
              });
            }
          });
        }
        
        // DOM mutation observer for dynamic content
        function observeDOMChanges() {
          if (!('MutationObserver' in window)) return;
          
          const observer = new MutationObserver(mutations => {
            let shouldScanImages = false;
            
            mutations.forEach(mutation => {
              if (mutation.type === 'childList' && mutation.addedNodes.length) {
                mutation.addedNodes.forEach(node => {
                  if (node.nodeName === 'IMG') {
                    // Direct image added
                    CacheManager.cacheImage(node.src, node);
                    if (node.dataset?.src) CacheManager.cacheImage(node.dataset.src, node);
                  } else if (node.nodeType === 1) {
                    // Check if added element contains images
                    if (node.querySelector('img')) shouldScanImages = true;
                  }
                });
              }
            });
            
            if (shouldScanImages) {
              CacheManager.cacheAllImages(document.body);
              setupLazyLoading();
            }
          });
          
          observer.observe(document.body, { 
            childList: true,
            subtree: true
          });
        }
        
        // Initialize everything
        async function init() {
          // Prepare IndexedDB
          await IndexedDB.init().catch(console.error);
          
          // Start caching all current images immediately
          CacheManager.cacheAllImages();
          
          // Setup lazy loading
          setupLazyLoading();
          
          // Observe DOM for dynamic content
          observeDOMChanges();
          
          // Default files to precache
          const precacheFiles = [
            '/'
          ].filter(Boolean);
          
          // Default dynamic patterns
          const dynamicPatterns = [
            '^https?://api\\.',
            '\\.(?:png|jpg|jpeg|gif|webp|svg)$',
            '\\.(?:woff2?|ttf|otf)$',
            '\\.(?:mp4|webm|mp3)$'
          ];
          
          // Register service worker
          registerServiceWorker(precacheFiles, dynamicPatterns);
          
          // Setup PWA install prompt
          setupInstallPrompt();
          
          return CacheManager;
        }
        
        // Make available globally but don't pollute window namespace
        window.ImageCache = {
          init,
          cacheImage: (url, elem) => CacheManager.cacheImage(url, elem),
          cacheAll: (container) => CacheManager.cacheAllImages(container),
          metrics: () => CacheManager.getMetrics()
        };
        
        // Auto-initialize if document is already loaded
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
          init();
        } else {
          document.addEventListener('DOMContentLoaded', init);
        }
      })();
    </script>

    <?php
      $rootDir = __DIR__;
      $excludeDirs = ['images', 'background_pictures', 'profile_pictures', 'thumbnails'];
      function getFilesRecursive($dir, $baseUrl = '') {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
          if ($file->isFile()) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['php', 'html', 'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico'])) {
              $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($dir)));
              $relativePath = ltrim($relativePath, '/');
              $isExcluded = false;
              foreach ($GLOBALS['excludeDirs'] as $exDir) {
                if (strpos($relativePath, $exDir . '/') === 0) { $isExcluded = true; break; }
              }
              // Add below: Exclude management.php explicitly
              if ($relativePath === 'management.php') {
                $isExcluded = true;
              }
              if (!$isExcluded) $files[] = $baseUrl . '/' . $relativePath;
            }
          }
        }
        return $files;
      }
      $fileList = getFilesRecursive($rootDir, '');
      $fileList = array_values(array_unique(array_filter($fileList, fn($f) => strpos($f, '/') === 0)));
      array_unshift($fileList, '/');
      if (!in_array('/icon/favicon.png', $fileList)) $fileList[] = '/icon/favicon.png';
      $jsFileList = json_encode($fileList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?>

    <!-- Manifest -->

    <script>
      const manifest = {
        "name": "ArtCODE",
        "short_name": "ArtCODE",
        "start_url": ".",
        "display": "standalone",
        "background_color": "#ffffff",
        "theme_color": "#000000",
        "icons": [
          { "src": "/icon/favicon.png", "sizes": "192x192", "type": "image/png" }
        ]
      };
      const manifestBlob = new Blob([JSON.stringify(manifest)], { type: 'application/json' });
      document.head.appendChild(Object.assign(document.createElement('link'), { rel: 'manifest', href: URL.createObjectURL(manifestBlob) }));

      // Register SW with all relevant files for offline/app-shell
      registerAppShellSW(<?php echo $jsFileList; ?>);
    </script>
