<?php
// admin/news/upload/index.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>Release News</title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../../../bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-auto">
          <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin/admin_header.php'); ?>
        </div>
        <div class="col overflow-auto vh-100">
          <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin/navbar.php'); ?>
          <div class="container py-4">
            <div id="uploadStatus"></div>
            <div class="row">
              <div class="col-6">
                <form id="uploadForm" enctype="multipart/form-data">
                  <div class="form-floating mb-3">
                    <input class="form-control rounded-3 border-0 rounded-4 p-3 bg-secondary-subtle focus-ring focus-ring-dark" type="text" name="title" id="title" placeholder="Enter title" maxlength="500" required>
                    <label for="title" class="fw-bold">Enter title</label>
                  </div>
                  <div class="form-floating mb-3">
                    <textarea class="form-control rounded-3 border-0 rounded-4 p-3 bg-secondary-subtle focus-ring focus-ring-dark" name="description" id="description" placeholder="Enter description" maxlength="5000" style="height: 450px;" required></textarea>
                    <label for="description" class="fw-bold">Enter description</label>
                  </div>
                  <div class="mb-2">
                    <button type="submit" class="btn bg-secondary-subtle w-100 fw-medium link-body-emphasis rounded-4">Release</button>
                  </div>
                </form>
              </div>
              <div class="col-6">
                <div id="newsPreview" class="border-0 rounded-4 p-3 bg-secondary-subtle">
                  <h3 id="previewTitle">Title Preview</h3>
                  <div id="previewDescription" class="mt-3">Description Preview</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include('../../../bootstrapjs.php'); ?>
    <script>
      function updatePreview() {
        const title = document.getElementById('title').value;
        const description = document.getElementById('description').value;

        // Save to local storage
        localStorage.setItem('newsTitle', title);
        localStorage.setItem('newsDescription', description);

        // Update title preview
        document.getElementById('previewTitle').textContent = title || 'Title Preview';

        // Convert newlines to <br> and process images and links
        document.getElementById('previewDescription').innerHTML = description ? processDescription(description) : 'Description Preview';
      }

      function processDescription(description) {
        // Convert newlines to <br>
        description = nl2br(description);

        // Replace image URLs with <img> tags
        description = description.replace(/(\bhttps?:\/\/\S+\.(png|jpg|jpeg|webp|gif|svg)\b)/gi, '<img src="$1" class="img-fluid" alt="Image">');

        // Replace YouTube URLs with embedded videos
        description = description.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/gi, function (match, videoId) {
          return '<div class="w-100 overflow-hidden position-relative ratio ratio-16x9"><iframe loading="lazy" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" class="rounded-4 position-absolute top-0 bottom-0 start-0 end-0 w-100 h-100 border-0 shadow" src="https://www.youtube.com/embed/' + videoId + '" frameborder="0" allowfullscreen></iframe></div>';
        });

        // Replace other URLs with clickable links
        description = description.replace(/(\bhttps?:\/\/\S+)/gi, '<a href="$1" target="_blank">$1</a>');

        return description;
      }

      function nl2br(str) {
        return str.replace(/\n/g, '<br>');
      }

      function loadFromLocalStorage() {
        const savedTitle = localStorage.getItem('newsTitle');
        const savedDescription = localStorage.getItem('newsDescription');

        if (savedTitle !== null) {
          document.getElementById('title').value = savedTitle;
        }
        if (savedDescription !== null) {
          document.getElementById('description').value = savedDescription;
          updatePreview();  // Update preview with saved content
        }
      }

      document.getElementById('title').addEventListener('input', updatePreview);
      document.getElementById('description').addEventListener('input', updatePreview);

      document.getElementById('uploadForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);

        fetch('upload_news.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          const statusAlert = document.createElement('div');
          statusAlert.className = `alert alert-${data.status} alert-dismissible fade show`;
          statusAlert.role = 'alert';
          statusAlert.innerHTML = `${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
          
          document.getElementById('uploadStatus').appendChild(statusAlert);

          // Clear local storage after successful upload
          if (data.status === 'success') {
            localStorage.removeItem('newsTitle');
            localStorage.removeItem('newsDescription');
            document.getElementById('uploadForm').reset();
            updatePreview();
          }
        })
        .catch(error => {
          const statusAlert = document.createElement('div');
          statusAlert.className = `alert alert-danger alert-dismissible fade show`;
          statusAlert.role = 'alert';
          statusAlert.innerHTML = `An error occurred: ${error.message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;

          document.getElementById('uploadStatus').appendChild(statusAlert);
        });
      });

      // Load saved data from local storage when the page loads
      document.addEventListener('DOMContentLoaded', loadFromLocalStorage);
    </script>
  </body>
</html>