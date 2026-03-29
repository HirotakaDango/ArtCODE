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
    animation: fade-out 0.25s ease both;
  }

  ::view-transition-new(root) {
    animation: fade-in 0.25s ease both;
  }

  @keyframes fade-out {
    to {
      opacity: 0;
    }
  }

  @keyframes fade-in {
    from {
      opacity: 0;
    }
  }

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
  const DB_NAME = 'imageCacheDB';
  const STORE_NAME = 'images';
  let db;

  function openDB() {
    return new Promise((resolve, reject) => {
      const req = indexedDB.open(DB_NAME, 1);
      req.onupgradeneeded = e => {
        const _db = e.target.result;
        if (!_db.objectStoreNames.contains(STORE_NAME)) {
          _db.createObjectStore(STORE_NAME);
        }
      };
      req.onsuccess = e => {
        db = e.target.result;
        resolve(db);
      };
      req.onerror = e => reject(e.target.error);
    });
  }

  function cacheImageDirect(url) {
    return new Promise((resolve, reject) => {
      const tx = db.transaction([STORE_NAME], 'readonly');
      const store = tx.objectStore(STORE_NAME);
      const getReq = store.get(url);
      getReq.onsuccess = async () => {
        if (getReq.result) {
          return resolve(getReq.result);
        }
        try {
          const resp = await fetch(url, { cache: 'force-cache' });
          if (!resp.ok) throw new Error();
          const blob = await resp.blob();
          const tx2 = db.transaction([STORE_NAME], 'readwrite');
          tx2.objectStore(STORE_NAME).put(blob, url);
          resolve(blob);
        } catch (err) {
          reject(err);
        }
      };
      getReq.onerror = () => reject();
    });
  }

  async function processImage(img) {
    const url = img.dataset.src || img.getAttribute('src');
    if (!url || url.startsWith('data:') || url.startsWith('blob:')) return;
    try {
      const blob = await cacheImageDirect(url);
      const objectUrl = URL.createObjectURL(blob);
      if (img.src !== objectUrl) {
        img.src = objectUrl;
      }
    } catch (e) {}
  }

  function loadAndCacheImages() {
    openDB().then(() => {
      document.querySelectorAll('img').forEach(processImage);
      const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
          mutation.addedNodes.forEach(node => {
            if (node.tagName === 'IMG') {
              processImage(node);
            } else if (node.querySelectorAll) {
              node.querySelectorAll('img').forEach(processImage);
            }
          });
        });
      });
      if (document.body) {
        observer.observe(document.body, { childList: true, subtree: true });
      }
    });
  }

  function registerAppShellSW(fileList) {
    if (!('serviceWorker' in navigator)) return;
    const swBlob = new Blob([`
      self.addEventListener('install', e => {
        e.waitUntil(
          caches.open('ArtCODE-v0.1.14').then(c => c.addAll(${JSON.stringify(fileList)}))
        );
      });
      self.addEventListener('fetch', e => {
        if (e.request.method !== 'GET') return;
        e.respondWith(
          caches.match(e.request).then(r => r || fetch(e.request))
        );
      });
    `], { type: 'application/javascript' });
    navigator.serviceWorker.register(URL.createObjectURL(swBlob));
  }

  document.addEventListener('DOMContentLoaded', () => {
    loadAndCacheImages();
    if ('BeforeInstallPromptEvent' in window) {
      let deferredPrompt;
      window.addEventListener('beforeinstallprompt', e => {
        e.preventDefault();
        deferredPrompt = e;
        const btn = document.getElementById('installButton');
        if (btn) {
          btn.style.display = 'block';
          btn.onclick = () => {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(choice => {
              if (choice.outcome === 'accepted') {
                btn.style.display = 'none';
              }
              deferredPrompt = null;
            });
          };
        }
      });
    }
  });
</script>

<?php
$rootDir = __DIR__;
$excludeDirs =['images', 'background_pictures', 'profile_pictures', 'thumbnails'];

function getFilesRecursive($dir, $baseUrl = '') {
  $files =[];
  $iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
  );
  foreach ($iterator as $file) {
    if ($file->isFile()) {
      $ext = strtolower($file->getExtension());
      if (in_array($ext,['php', 'html', 'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico'])) {
        $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($dir)));
        $relativePath = ltrim($relativePath, '/');
        $isExcluded = false;
        foreach ($GLOBALS['excludeDirs'] as $exDir) {
          if (strpos($relativePath, $exDir . '/') === 0) {
            $isExcluded = true;
            break;
          }
        }
        if ($relativePath === 'management.php') {
          $isExcluded = true;
        }
        if (!$isExcluded) {
          $files[] = $baseUrl . '/' . $relativePath;
        }
      }
    }
  }
  return $files;
}

$fileList = getFilesRecursive($rootDir, '');
$fileList = array_values(array_unique(array_filter($fileList, fn($f) => strpos($f, '/') === 0)));
array_unshift($fileList, '/');
if (!in_array('/icon/favicon.png', $fileList)) {
  $fileList[] = '/icon/favicon.png';
}
$jsFileList = json_encode($fileList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>

<script>
  const manifest = {
    "name": "ArtCODE",
    "short_name": "ArtCODE",
    "start_url": ".",
    "display": "standalone",
    "background_color": "#ffffff",
    "theme_color": "#000000",
    "icons":[
      {
        "src": "/icon/favicon.png",
        "sizes": "192x192",
        "type": "image/png"
      }
    ]
  };
  const manifestBlob = new Blob([JSON.stringify(manifest)], { type: 'application/json' });
  const manifestLink = document.createElement('link');
  manifestLink.rel = 'manifest';
  manifestLink.href = URL.createObjectURL(manifestBlob);
  document.head.appendChild(manifestLink);

  registerAppShellSW(<?php echo $jsFileList; ?>);
</script>