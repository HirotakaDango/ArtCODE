    <!-- Share Modal -->
    <div class="modal fade" id="shareLink" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fw-bold fs-5" id="exampleModalLabel">share to:</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- First Social Media Section -->
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- Twitter -->
              <a class="btn btn-outline-dark" href="https://twitter.com/intent/tweet?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>">
                <i class="bi bi-twitter"></i>
              </a>
                
              <!-- Line -->
              <a class="btn btn-outline-dark" href="https://social-plugins.line.me/lineit/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-line"></i>
              </a>
                
              <!-- Email -->
              <a class="btn btn-outline-dark" href="mailto:?body=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>">
                <i class="bi bi-envelope-fill"></i>
              </a>
                
              <!-- Reddit -->
              <a class="btn btn-outline-dark" href="https://www.reddit.com/submit?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-reddit"></i>
              </a>
                
              <!-- Instagram -->
              <a class="btn btn-outline-dark" href="https://www.instagram.com/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i>
              </a>
                
              <!-- Facebook -->
              <a class="btn btn-outline-dark" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i>
              </a>
            </div>
            <!-- Second Social Media Section -->
            <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
              <!-- WhatsApp -->
              <a class="btn btn-outline-dark" href="https://wa.me/?text=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i>
              </a>
  
              <!-- Pinterest -->
              <a class="btn btn-outline-dark" href="https://pinterest.com/pin/create/button/?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-pinterest"></i>
              </a>
  
              <!-- LinkedIn -->
              <a class="btn btn-outline-dark" href="https://www.linkedin.com/shareArticle?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-linkedin"></i>
              </a>
  
              <!-- Messenger -->
              <a class="btn btn-outline-dark" href="https://www.facebook.com/dialog/send?link=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
              </a>
  
              <!-- Telegram -->
              <a class="btn btn-outline-dark" href="https://telegram.me/share/url?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-telegram"></i>
              </a>
  
              <!-- Snapchat -->
              <a class="btn btn-outline-dark" href="https://www.snapchat.com/share?url=<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/?artworkid=' . $image['id']; ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-snapchat"></i>
              </a>
            </div>
            <!-- End -->
            <div class="input-group mb-2">
              <input type="text" id="urlInput1" value="<?php echo 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" class="form-control border-2 fw-bold" readonly>
              <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard1()">
                <i class="bi bi-clipboard-fill"></i>
              </button>
            </div>
            <button class="btn btn-secondary opacity-50 fw-bold w-100" onclick="sharePage()"><i class="bi bi-share-fill"></i> <small>share</small></button>
          </div>
        </div>
      </div>
    </div>