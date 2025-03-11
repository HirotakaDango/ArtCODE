    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" rel="preload" as="style">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="preload" as="style">
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
      document.addEventListener('DOMContentLoaded', function() {
        // Enable the view transition API
        if ('viewTransition' in document) {
          document.documentElement.setAttribute('view-transition', 'enabled');
        }

        // Start caching images and content
        cacheImagesAndContent();
      });

      // IndexedDB setup for caching images
      const DB_NAME = 'imageCacheDB';
      const DB_VERSION = 1;
      const STORE_NAME = 'images';

      let db;

      // Initialize IndexedDB database
      function initDB() {
        return new Promise((resolve, reject) => {
          const request = indexedDB.open(DB_NAME, DB_VERSION);

          request.onupgradeneeded = function (e) {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
              db.createObjectStore(STORE_NAME);
            }
          };

          request.onsuccess = function (e) {
            db = e.target.result;
            resolve(db);
          };

          request.onerror = function (e) {
            reject('Error opening IndexedDB: ' + e.target.error);
          };
        });
      }

      // Function to store image in IndexedDB
      function storeImageInDB(imageUrl, imageBlob) {
        return new Promise((resolve, reject) => {
          const transaction = db.transaction([STORE_NAME], 'readwrite');
          const store = transaction.objectStore(STORE_NAME);
          const request = store.put(imageBlob, imageUrl);

          request.onsuccess = function () {
            resolve();
          };

          request.onerror = function () {
            reject('Error storing image in DB');
          };
        });
      }

      // Function to get image from IndexedDB
      function getImageFromDB(imageUrl) {
        return new Promise((resolve, reject) => {
          const transaction = db.transaction([STORE_NAME], 'readonly');
          const store = transaction.objectStore(STORE_NAME);
          const request = store.get(imageUrl);

          request.onsuccess = function () {
            resolve(request.result); // Return the image Blob
          };

          request.onerror = function () {
            reject('Error retrieving image from DB');
          };
        });
      }

      // Function to cache image
      async function cacheImage(imageUrl) {
        try {
          const cachedImage = await getImageFromDB(imageUrl);
          if (cachedImage) {
            return cachedImage; // Return cached image from IndexedDB
          }

          const response = await fetch(imageUrl);
          const imageBlob = await response.blob();
          await storeImageInDB(imageUrl, imageBlob); // Store image in IndexedDB
          return imageBlob;
        } catch (error) {
          console.error(error);
        }
      }

      // Function to lazy load and cache images when in the viewport
      function lazyLoadAndCacheImages() {
        const images = document.querySelectorAll('img[data-src]'); // Select images with data-src attribute
        const imageObserver = new IntersectionObserver((entries, observer) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const img = entry.target;
              if (img.dataset.src && !img.src) {
                img.src = URL.createObjectURL(await cacheImage(img.dataset.src)); // Set src using Blob URL
                img.removeAttribute('data-src');
              }
              observer.unobserve(img); // Stop observing the image
            }
          });
        });

        images.forEach(img => {
          if (img.src) {
            cacheImage(img.src); // Cache already loaded images
          }
          imageObserver.observe(img); // Start observing each image
        });
      }

      // Cache images and content
      function cacheImagesAndContent() {
        initDB().then(() => {
          lazyLoadAndCacheImages(); // Start caching images

          // Cache page content when clicked on <button> or <a> links
          document.querySelectorAll('button, a').forEach(element => {
            element.addEventListener('click', async function(event) {
              const url = this.href || location.href; // For <a> elements, get the URL from href
              if (!url) return;

              const response = await fetch(url);
              if (response.ok) {
                const content = await response.text();
                localStorage.setItem(url, content); // Cache the page content in localStorage
              }
            });
          });

          // Check if page content is already cached in localStorage
          const cachedPage = localStorage.getItem(location.href);
          if (cachedPage) {
            document.body.innerHTML = cachedPage; // Load from cache
          }
        }).catch(error => {
          console.error('Error initializing database:', error);
        });
      }
    </script>
    
    <script>
        const installButton = document.getElementById('installButton');

        // Check if the browser supports the beforeinstallprompt event
        if ('BeforeInstallPromptEvent' in window) {
            let deferredPrompt;

            window.addEventListener('beforeinstallprompt', (e) => {
                console.log('beforeinstallprompt fired');
                e.preventDefault(); // Prevent the default prompt
                deferredPrompt = e; // Stash the event for later use

                installButton.style.display = 'block'; // Make the install button visible
                installButton.addEventListener('click', () => {
                    console.log('Install button clicked');
                    if (deferredPrompt) {
                        deferredPrompt.prompt(); // Show the install prompt
                        deferredPrompt.userChoice.then((choiceResult) => {
                            if (choiceResult.outcome === 'accepted') {
                                console.log('User accepted the install prompt');
                                installButton.style.display = 'none'; // Hide button after install
                            } else {
                                console.log('User dismissed the install prompt');
                            }
                            deferredPrompt = null; // Clear the deferredPrompt since it can only be used once
                        });
                    }
                });
            });

            // Service Worker Registration (Embedded as a String - More complex logic is better in a separate file)
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register(URL.createObjectURL(new Blob([`
                    self.addEventListener('install', function(event) {
                        console.log('Service Worker: Install Event in single file');
                        event.waitUntil(
                            caches.open('v1').then(function(cache) {
                                return cache.addAll([
                                    './', // Cache the index.html (or whatever your main file is)
                                    'icon-192x192.png' // Placeholder icon - make sure this file exists or remove
                                ]);
                            })
                        );
                    });

                    self.addEventListener('fetch', function(event) {
                        event.respondWith(
                            caches.match(event.request).then(function(response) {
                                return response || fetch(event.request);
                            })
                        );
                    });
                `], { type: 'text/javascript' }))); // Create a Blob URL for the service worker code
            }
        } else {
            installButton.style.display = 'none'; // Hide install button if install prompt not supported
            console.log('Install prompt not supported in this browser.');
        }
    </script>