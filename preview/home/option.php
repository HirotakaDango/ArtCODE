    <?php
    // Generate the query strings for the buttons
    $fullHomepageHref = http_build_query(array_merge($_GET, ['option' => 'full']));
    $simpleHomepageHref = http_build_query(array_merge($_GET, ['option' => 'simple']));
    ?>
    
    <div class="d-flex justify-content-end">
      <div class="px-1 overflow-auto text-nowrap">
        <a href="javascript:void(0);" id="full-homepage-button" class="btn fw-medium btn-sm btn-dark rounded-pill <?php if (isset($_GET['option']) && $_GET['option'] == 'full') echo 'active'; ?>">full homepage</a>
        <a href="javascript:void(0);" id="simple-homepage-button" class="btn fw-medium btn-sm btn-dark rounded-pill <?php if (!isset($_GET['option']) || $_GET['option'] == 'simple') echo 'active'; ?>">simple homepage</a>
      </div>
    </div>
    <?php include('tags_group.php'); ?>
    <h3 class="px-2 mt-3 fw-bold">Discover</h3>
    <div class="dropdown">
      <button class="btn btn-sm fw-bold rounded-pill ms-2 mb-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-images"></i> sort by
      </button>
      <ul class="dropdown-menu">
        <?php
        // Get current query parameters, excluding 'by' and 'page'
        $queryParams = array_diff_key($_GET, array('by' => '', 'page' => ''));
        
        // Define sorting options and labels
        $sortOptions = [
          'newest' => 'newest',
          'oldest' => 'oldest',
          'popular' => 'popular',
          'view' => 'most viewed',
          'least' => 'least viewed',
          'liked' => 'liked',
          'order_asc' => 'from A to Z',
          'order_desc' => 'from Z to A',
          'top' => 'top images'
        ];
    
        // Loop through each sort option
        foreach ($sortOptions as $key => $label) {
          // Determine if the current option is active
          $activeClass = (!isset($_GET['by']) && $key === 'newest') || (isset($_GET['by']) && $_GET['by'] === $key) ? 'active' : '';
          
          // Generate the dropdown item with the appropriate active class
          echo '<li><a href="?' . http_build_query(array_merge($queryParams, ['by' => $key, 'page' => isset($_GET['page']) ? $_GET['page'] : '1'])) . '" class="dropdown-item fw-bold ' . $activeClass . '">' . $label . '</a></li>';
        }
        ?>
      </ul>
    </div>
    <script>
      // Function to determine display type based on some criteria
      function getDisplayType() {
        return window.innerWidth <= 768 ? 'mobile' : 'desktop'; // Example condition
      }
    
      // Function to update href attributes with display type and existing parameters
      function updateLinks() {
        const displayType = getDisplayType();
      
        // Get the existing query parameters
        const currentParams = new URLSearchParams(window.location.search);
    
        // Update the hrefs for the buttons
        document.getElementById('full-homepage-button').href = "?" + new URLSearchParams({ ...Object.fromEntries(currentParams.entries()), option: 'full', display: displayType }).toString();
        document.getElementById('simple-homepage-button').href = "?" + new URLSearchParams({ ...Object.fromEntries(currentParams.entries()), option: 'simple', display: displayType }).toString();
      }
    
      // Update links on page load and on resize
      document.addEventListener('DOMContentLoaded', updateLinks);
      window.addEventListener('resize', updateLinks);

      // Function to save the option in localStorage
      function saveOption(option) {
        localStorage.setItem('homepage_option', option);
      }

      // Ensure that if the user has a stored preference, the URL is updated accordingly
      document.addEventListener('DOMContentLoaded', function() {
        const storedOption = localStorage.getItem('homepage_option');
        const urlParams = new URLSearchParams(window.location.search);
        const urlOption = urlParams.get('option');

        // If there's a stored option but no URL parameter, redirect to the stored option with existing parameters
        if (storedOption && !urlOption) {
          // Add or update the 'option' parameter
          urlParams.set('option', storedOption);
          // Construct the new URL with updated parameters
          window.location.href = window.location.pathname + '?' + urlParams.toString();
        }

        // If the URL has an option parameter, save it in localStorage
        if (urlOption) {
          saveOption(urlOption);
        }
      });
    </script>