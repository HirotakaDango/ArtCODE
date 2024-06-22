<?php
$email = $_SESSION['email'];

// Connect to the SQLite database using parameterized query
$dbD = new SQLite3('database.sqlite');

// Get all of the images from the database using parameterized query
$stmtD = $dbD->prepare("SELECT * FROM images ORDER BY id DESC");
$resultD = $stmtD->execute();

$images = array();
while ($imageD = $resultD->fetchArray()) {
  $imageData = array(
    'id' => $imageD['id'],
    'filename' => $imageD['filename'],
    'title' => $imageD['title'],
    // Add more fields as needed
  );
  $images[] = $imageData;
}
?>
  
    <div class="imagesLT w-100 px-1 my-2">
      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-5 g-1">
        <?php 
        $count = 0; 
        $images = [];
        while ($imageD = $resultD->fetchArray()) {
          $images[] = $imageD;
          if ($count < 10) {
            $image_idD = $imageD['id'];
            $image_urlD = $imageD['filename'];
            $image_titleD = $imageD['title'];
            $current_image_idD = isset($_GET['artworkid']) ? $_GET['artworkid'] : null;
        ?>
        <div class="col">
          <div class="position-relative">
            <a class="rounded ratio ratio-1x1 imageLTA" href="image.php?artworkid=<?php echo $image_idD; ?>">
              <img class="rounded shadow object-fit-cover imageLTID <?php echo ($imageD['type'] === 'nsfw') ? 'nsfw' : ''; ?> <?php echo ($image_idD == $current_image_idD) ? 'opacity-50' : ''; ?>" src="/thumbnails/<?php echo $image_urlD; ?>" alt="<?php echo $image_titleD; ?>">
            </a>
          </div>
        </div>
        <?php 
            $count++;
          }
        } 
        ?>
      </div>
    </div>
    <div class="container-fluid mt-4">
      <button id="loadMoreBtnD" class="btn btn-outline-dark rounded-pill fw-bold w-100">Load more</button>
    </div>
    <script>
      var currentIndexD = 10;
      var imagesD = <?php echo json_encode($images); ?>;
      var containerD = document.querySelector('.imagesLT .row');
      var loadMoreBtnD = document.getElementById('loadMoreBtnD');
    
      loadMoreBtnD.addEventListener('click', function() {
        var fragment = document.createDocumentFragment();
    
        for (var i = currentIndexD; i < currentIndexD + 10 && i < imagesD.length; i++) {
          var imageUD = imagesD[i];
          var image_idD = imageUD['id'];
          var image_urlD = imageUD['filename'];
          var image_titleD = imageUD['title'];
          var current_image_idD = '<?php echo $current_image_idD; ?>';
    
          var colDiv = document.createElement('div');
          colDiv.classList.add('col');
    
          var posRelDiv = document.createElement('div');
          posRelDiv.classList.add('position-relative');
    
          var linkD = document.createElement('a');
          linkD.href = 'image.php?artworkid=' + image_idD;
          linkD.classList.add('rounded', 'ratio', 'ratio-1x1', 'imageLTA');
    
          var imageD = document.createElement('img');
          imageD.classList.add('rounded', 'shadow', 'object-fit-cover', 'imageLTID');
          if (imageUD['type'] === 'nsfw') {
            imageD.classList.add('nsfw');
          }
          if (image_idD == current_image_idD) {
            imageD.classList.add('opacity-50');
          }
          imageD.src = '/thumbnails/' + image_urlD;
          imageD.alt = image_titleD;
    
          linkD.appendChild(imageD);
          posRelDiv.appendChild(linkD);
          colDiv.appendChild(posRelDiv);
          fragment.appendChild(colDiv);
        }
    
        containerD.appendChild(fragment);
    
        currentIndexD += 10;
        if (currentIndexD >= imagesD.length) {
          loadMoreBtnD.style.display = 'none';
        }
      });
    </script>
    <style>
      .text-stroke {
        -webkit-text-stroke: 1px;
      }
    </style>
    <script>
      function shareImageL(userId) {
        // Compose the share URL
        var shareUrl = '?artworkid=' + userId;

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