<?php
// admin/news/index.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <title>Admin News Dashboard</title>
    <?php include('../../bootstrapcss.php'); ?>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <div class="container-fluid px-0">
      <div class="row g-0">
        <div class="col-auto">
          <?php include('../admin_header.php'); ?>
        </div>
        <div class="col overflow-auto vh-100">
          <?php include('../navbar.php'); ?>
          <div class="container py-4">
            <h1 class="fw-bold text-center">All News</h1>
            <div class="mb-3">
              <select id="sortOrder" class="form-select rounded border-0 bg-secondary-subtle" style="width: auto;">
                <option value="newest" selected>Newest</option>
                <option value="oldest">Oldest</option>
              </select>
            </div>
            <div id="newsContainer" class="row row-cols-2 g-2"></div>
          </div>
        </div>
      </div>
    </div>
    <script>
      function getYouTubeVideoId(url) {
        const pattern = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
        const match = url.match(pattern);
        return match ? match[1] : '';
      }

      function formatDescription(description) {
        return description.replace(/(\bhttps?:\/\/\S+)/gi, (url) => {
          if (/\.(png|jpg|jpeg|webp|gif)$/i.test(url)) {
            return `<a href="${url}" target="_blank"><img class="w-100 rounded-4" src="${url}" alt="Image"></a>`;
          } else if (url.includes('youtube.com') || url.includes('youtu.be')) {
            const videoId = getYouTubeVideoId(url);
            return videoId ? `<div class="ratio ratio-16x9"><iframe src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen></iframe></div>` : `<a href="${url}">${url}</a>`;
          } else {
            return `<a href="${url}">${url}</a>`;
          }
        });
      }

      function loadNews(sortOrder) {
        $.ajax({
          url: 'news_load.php',
          method: 'GET',
          data: { sort: sortOrder },
          success: function(data) {
            let newsHtml = '';
            data.forEach(news => {
              newsHtml += `
                <div class="col">
                  <div class="card h-100 rounded-4 border-0 bg-secondary-subtle p-3 position-relative">
                    <div class="card-body">
                      <h3 class="mb-4">${news.title}</h3>
                      <p>${formatDescription(news.description)}</p>
                      <p><small>${news.created_at}</small></p>
                      <button class="btn border-0 btn-sm delete-news position-absolute top-0 end-0 m-2" data-id="${news.id}"><i class="bi bi-trash3-fill link-body-emphasis"></i></button>
                    </div>
                  </div>
                </div>
              `;
            });
            $('#newsContainer').html(newsHtml);
          }
        });
      }

      $(document).ready(function() {
        // Load news on page load
        loadNews('newest');
        // Load news when the sort order changes
        $('#sortOrder').change(function() {
          let sortOrder = $(this).val();
          loadNews(sortOrder);
        });

        // Handle delete button click
        $('#newsContainer').on('click', '.delete-news', function() {
          const newsId = $(this).data('id');
          console.log(`Deleting news ID: ${newsId}`); // Debugging line
          if (confirm('Are you sure you want to delete this news item?')) {
            $.ajax({
              url: 'delete.php',
              method: 'POST',
              data: { news_id: newsId },
              success: function(response) {
                console.log(response); // Debugging line
                if (response.success) {
                  alert('News item deleted successfully.');
                  loadNews($('#sortOrder').val()); // Reload news
                } else {
                  alert('Error: ' + response.error);
                }
              }
            });
          }
        });
      });
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>