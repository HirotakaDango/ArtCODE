                <?php
                  $domain = $_SERVER['HTTP_HOST'];
                  $imageId = $imageL['id'];
                  $url = "http://$domain/image.php?artworkid=$imageId";
                ?>
                <div class="modal fade" id="shareImage<?php echo $imageId; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-transparent border-0 rounded-0">
                      <div class="card rounded-4 p-4">
                        <p class="text-start fw-bold">share to:</p>
                        <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                          <!-- Twitter -->
                          <a class="btn rounded-start-4" href="https://twitter.com/intent/tweet?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-twitter"></i>
                          </a>
                                            
                          <!-- Line -->
                          <a class="btn" href="https://social-plugins.line.me/lineit/share?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-line"></i>
                          </a>
                                            
                          <!-- Email -->
                          <a class="btn" href="mailto:?body=<?php echo $url; ?>">
                            <i class="bi bi-envelope-fill"></i>
                          </a>
                                            
                          <!-- Reddit -->
                          <a class="btn" href="https://www.reddit.com/submit?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-reddit"></i>
                          </a>
                                            
                          <!-- Instagram -->
                          <a class="btn" href="https://www.instagram.com/?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-instagram"></i>
                          </a>
                                            
                          <!-- Facebook -->
                          <a class="btn rounded-end-4" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-facebook"></i>
                          </a>
                        </div>
                        <div class="btn-group w-100 mb-2" role="group" aria-label="Share Buttons">
                          <!-- WhatsApp -->
                          <a class="btn rounded-start-4" href="https://wa.me/?text=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp"></i>
                          </a>
                
                          <!-- Pinterest -->
                          <a class="btn" href="https://pinterest.com/pin/create/button/?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-pinterest"></i>
                          </a>
                
                          <!-- LinkedIn -->
                          <a class="btn" href="https://www.linkedin.com/shareArticle?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-linkedin"></i>
                          </a>
                
                          <!-- Messenger -->
                          <a class="btn" href="https://www.facebook.com/dialog/send?link=<?php echo $url; ?>&app_id=YOUR_FACEBOOK_APP_ID" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-messenger"></i>
                          </a>
                
                          <!-- Telegram -->
                          <a class="btn" href="https://telegram.me/share/url?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-telegram"></i>
                          </a>
                
                          <!-- Snapchat -->
                          <a class="btn rounded-end-4" href="https://www.snapchat.com/share?url=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-snapchat"></i>
                          </a>
                        </div>
                        <div class="input-group">
                          <input type="text" id="urlInput_<?php echo $imageId; ?>" value="<?php echo $url; ?>" class="form-control border-2 fw-bold" readonly>
                          <button class="btn btn-secondary opacity-50 fw-bold" onclick="copyToClipboard_<?php echo $imageId; ?>()">
                            <i class="bi bi-clipboard-fill"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <script>
                  function copyToClipboard_<?php echo $imageId; ?>() {
                    var urlInput = document.getElementById('urlInput_<?php echo $imageId; ?>');
                    urlInput.select();
                    urlInput.setSelectionRange(0, 99999); // For mobile devices
                
                    try {
                      // Modern clipboard API
                      navigator.clipboard.writeText(urlInput.value).then(function() {
                        console.log('Text copied to clipboard');
                      }, function(err) {
                        console.error('Failed to copy text: ', err);
                      });
                    } catch (err) {
                      // Fallback to document.execCommand if Clipboard API is not supported
                      document.execCommand('copy');
                    }
                  }
                </script>