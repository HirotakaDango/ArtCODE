<?php
require_once('image_code.php');
?> 

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $image['title']; ?></title>
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('../contents/header.php'); ?>
    <div class="mt-2">
      <?php include('user_info.php'); ?>
      <div class="roow">
        <?php include('col-1.php'); ?>
        <?php include('col-2.php'); ?>
      </div>
    </div>
    <?php include('share.php'); ?>
    <style>
      .img-pointer {
        transition: opacity 0.3s ease-in-out;
      }
    
      .img-pointer:hover {
        opacity: 0.8;
        cursor: pointer;
      }
      
      .img-blur {
        filter: blur(2px);
      }
      
      .text-stroke-2 {
        -webkit-text-stroke: 3px;
      }
      
      .media-scrollerF {
        display: grid;
        gap: 3px; /* Updated gap value */
        grid-auto-flow: column;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
      }

      .snaps-inlineF {
        scroll-snap-type: inline mandatory;
        scroll-padding-inline: var(--_spacer, 1rem);
      }

      .snaps-inlineF > * {
        scroll-snap-align: start;
      }
  
      .scroll-container {
        scrollbar-width: none;  /* Firefox */
        -ms-overflow-style: none;  /* Internet Explorer 10+ */
        margin-left: auto;
        margin-right: auto;
      }
      
      .w-98 {
        width: 98%;
      }

      .scroll-container::-webkit-scrollbar {
        width: 0;  /* Safari and Chrome */
        height: 0;
      }
      
      .scrollable-div {
        overflow: auto;
        scrollbar-width: thin;  /* For Firefox */
        -ms-overflow-style: none;  /* For Internet Explorer and Edge */
        scrollbar-color: transparent transparent;  /* For Chrome, Safari, and Opera */
      }

      .scrollable-div::-webkit-scrollbar {
        width: 0;
        background-color: transparent;
      }
      
      .scrollable-div::-webkit-scrollbar-thumb {
        background-color: transparent;
      }

      .image-containerA {
        width: 33.33%;
        flex-grow: 1;
      }
  
      .text-sm {
        font-size: 13px;
      }
      
      .display-f {
        font-size: 33px;
      } 

      .roow {
        display: flex;
        flex-wrap: wrap;
      }

      .cool-6 {
        width: 50%;
        padding: 0 15px;
        box-sizing: border-box;
      }

      .caard {
        margin-bottom: 15px;
      }
      
      .rounded-r {
        border-radius: 15px;
      }

      .scrollable-title::-webkit-scrollbar {
        width: 0;
        height: 0;
      }
  
      @media (max-width: 767px) {
        .cool-6 {
          width: 100%;
          padding: 0;
        }
        
        .display-small-none {
          display: none;
        }
        
        .rounded-r {
          border-radius: 0;
        }

        .img-UF {
          width: 100%;
          height: 200px;
        }
      }
      
      @media (min-width: 768px) {
        .img-UF {
          width: 100%;
          height: 300px;
        }
      }
      
      .overlay {
        position: relative;
        display: flex;
        flex-direction: column; /* Change to column layout */
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Adjust background color and opacity */
        text-align: center;
        position: absolute;
        top: 0;
        left: 0;
      }

      .overlay i {
        font-size: 48px; /* Adjust icon size */
      }

      .overlay span {
        font-size: 18px; /* Adjust text size */
        margin-top: 8px; /* Add spacing between icon and text */
      }
    </style> 
    <p class="text-secondary fw-bold ms-2 mt-2">Latest Images</p>
    <?php
      include 'latest.php';
    ?>
    <p class="text-secondary fw-bold ms-2 mt-4">Popular Images</p>
    <?php
      include 'most_popular.php';
    ?>
    <div class="mt-5"></div>
    <script>
      function copyToClipboard() {
        var urlInput = document.getElementById('urlInput');
        urlInput.select();
        urlInput.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }

      function copyToClipboard1() {
        var urlInput1 = document.getElementById('urlInput1');
        urlInput1.select();
        urlInput1.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand('copy');
      }
      
      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton = document.getElementById("toggleButton");
        const caretIcon = toggleButton.querySelector("i");
        const collapseExample = document.getElementById("collapseExample");

        toggleButton.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          }
        });

        collapseExample.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
        });

        collapseExample.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
        });
      });

      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton1 = document.getElementById("toggleButton1");
        const caretIcon = toggleButton1.querySelector("i");
        const collapseExample = document.getElementById("collapseExample1");

        toggleButton1.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          }
        });

        collapseExample.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
        });

        collapseExample.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
        });
      });

      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton2 = document.getElementById("toggleButton2");
        const caretIcon = toggleButton2.querySelector("i");
        const collapseExample = document.getElementById("collapseDataImage");

        toggleButton2.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          }
        });

        collapseExample.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
        });

        collapseExample.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
        });
      });

      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton3 = document.getElementById("toggleButton3");
        const caretIcon = toggleButton3.querySelector("i");
        const collapseExample = document.getElementById("collapseDataImage1");

        toggleButton3.addEventListener("click", function() {
          if (caretIcon.classList.contains("bi-caret-down-fill")) {
            caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
            toggleButton3.innerHTML = "<i class='bi bi-caret-down-fill'></i> <small>show more</small>";
          } else {
            caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
            toggleButton3.innerHTML = "<i class='bi bi-caret-up-fill'></i> <small>show less</small>";
          }
        });

        collapseExample.addEventListener("hidden.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-up-fill", "bi-caret-down-fill");
          toggleButton3.innerHTML = "<i class='bi bi-caret-down-fill'></i> <small>show more</small>";
        });

        collapseExample.addEventListener("shown.bs.collapse", function () {
          caretIcon.classList.replace("bi-caret-down-fill", "bi-caret-up-fill");
          toggleButton3.innerHTML = "<i class='bi bi-caret-up-fill'></i> <small>show less</small>";
        });
      });
    </script>
    <script>
      var originalImageLink = document.getElementById("originalImageLink");
      var originalImage = document.getElementById("originalImage");
      var originalImageSrc = originalImageLink.getAttribute("data-original-src");

      originalImageLink.addEventListener("click", function(event) {
        event.preventDefault();
        originalImage.setAttribute("src", originalImageSrc);
      });

      var modal = document.getElementById("originalImageModal");
      modal.addEventListener("hidden.bs.modal", function() {
        originalImage.setAttribute("src", "");
      });

      modal.addEventListener("shown.bs.modal", function() {
        originalImage.setAttribute("src", originalImageSrc);
      });

      // Update the Load Original button functionality
      var loadOriginalBtn = document.getElementById("loadOriginalBtn");
      var showProgressBtn = document.getElementById("showProgressBtn");
      var thumbnailImage = document.querySelector("#originalImageLink img");

      loadOriginalBtn.addEventListener("click", function(event) {
        event.preventDefault();

        var originalSrc = originalImageLink.getAttribute("data-original-src");
        thumbnailImage.setAttribute("src", originalSrc);

        // Hide the "loadOriginalBtn" after it's clicked
        loadOriginalBtn.style.display = "none";

        // Show the "showProgressBtn" to indicate progress
        showProgressBtn.style.display = "block";

        var xhr = new XMLHttpRequest();
        xhr.open("GET", originalSrc, true);
        xhr.responseType = "blob";

        xhr.onprogress = function(event) {
          if (event.lengthComputable) {
            var percentLoaded = (event.loaded / event.total) * 100;
            showProgressBtn.textContent = "Loading Image: " + percentLoaded.toFixed(2) + "% (<?php echo $images_total_size; ?> MB)";
          }
        };

        xhr.onload = function() {
          var blob = xhr.response;
          var objectURL = URL.createObjectURL(blob);
          thumbnailImage.setAttribute("src", objectURL);
          // Hide the progress button when loading is complete
          showProgressBtn.style.display = "none";
        };

        xhr.send();
      });
    </script>
    <script>
      function sharePage() {
        if (navigator.share) {
          navigator.share({
            title: document.title,
            url: window.location.href
          }).then(() => {
            console.log('Page shared successfully.');
          }).catch((error) => {
            console.error('Error sharing page:', error);
          });
        } else {
          console.log('Web Share API not supported.');
        }
      }
    </script>
    <script>
      function shareArtist(userId) {
        // Compose the share URL
        var shareUrl = 'artist.php?id=' + userId;

        // Check if the Share API is supported by the browser
        if (navigator.share) {
          navigator.share({
          url: shareUrl
        })
          .then(() => console.log('Shared successfully.'))
          .catch((error) => console.error('Error sharing:', error));
        } else {
          console.log('Share API is not supported in this browser.');
          // Provide an alternative action for browsers that do not support the Share API
          // For example, you can open a new window with the share URL
          window.open(shareUrl, '_blank');
        }
      }
    </script>
    <script>
      let lazyloadImages = document.querySelectorAll(".lazy-load");
      let imageContainer = document.getElementById("image-container");

      // Set the default placeholder image
      const defaultPlaceholder = "icon/bg.png";

      if ("IntersectionObserver" in window) {
        let imageObserver = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              let image = entry.target;
              image.src = image.dataset.src;
              imageObserver.unobserve(image);
            }
          });
        });

        lazyloadImages.forEach(function(image) {
          image.src = defaultPlaceholder; // Apply default placeholder
          imageObserver.observe(image);
          image.style.filter = "blur(5px)"; // Apply initial blur to all images

          // Remove blur and apply custom blur to NSFW images after they load
          image.addEventListener("load", function() {
            image.style.filter = ""; // Remove initial blur
            if (image.classList.contains("nsfw")) {
              image.style.filter = "blur(4px)"; // Apply blur to NSFW images
          
              // Add overlay with icon and text
              let overlay = document.createElement("div");
              overlay.classList.add("overlay", "rounded");
              let icon = document.createElement("i");
              icon.classList.add("bi", "bi-eye-slash-fill", "text-white");
              overlay.appendChild(icon);
              let text = document.createElement("span");
              text.textContent = "R-18";
              text.classList.add("shadowed-text", "fw-bold", "text-white");
              overlay.appendChild(text);
              image.parentNode.appendChild(overlay);
            }
          });
        });
      } else {
        let lazyloadThrottleTimeout;

        function lazyload() {
          if (lazyloadThrottleTimeout) {
            clearTimeout(lazyloadThrottleTimeout);
          }
          lazyloadThrottleTimeout = setTimeout(function() {
            let scrollTop = window.pageYOffset;
            lazyloadImages.forEach(function(img) {
              if (img.offsetTop < window.innerHeight + scrollTop) {
                img.src = img.dataset.src;
                img.classList.remove("lazy-load");
              }
            });
            lazyloadImages = Array.from(lazyloadImages).filter(function(image) {
              return image.classList.contains("lazy-load");
            });
            if (lazyloadImages.length === 0) {
              document.removeEventListener("scroll", lazyload);
              window.removeEventListener("resize", lazyload);
              window.removeEventListener("orientationChange", lazyload);
            }
          }, 20);
        }

        document.addEventListener("scroll", lazyload);
        window.addEventListener("resize", lazyload);
        window.addEventListener("orientationChange", lazyload);
      }

      // Infinite scrolling
      let loading = false;

      function loadMoreImages() {
        if (loading) return;
        loading = true;

        // Simulate loading delay for demo purposes
        setTimeout(function() {
          for (let i = 0; i < 10; i++) {
            if (lazyloadImages.length === 0) {
              break;
            }
            let image = lazyloadImages[0];
            imageContainer.appendChild(image);
            lazyloadImages = Array.from(lazyloadImages).slice(1);
          }
          loading = false;
        }, 1000);
      }

      window.addEventListener("scroll", function() {
        if (window.innerHeight + window.scrollY >= imageContainer.clientHeight) {
          loadMoreImages();
        }
      });

      // Initial loading
      loadMoreImages();
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>