<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - How to Use</title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
      @media (min-width: 778px) {
        .sidebar {
          position: fixed;
          height: 100vh;
        }
      }
    </style>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block shadow-lg sidebar d-none d-md-block">
          <div class="position-responsive">
            <h4 class="mt-2">Documentation</h4>
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link active" href="#endpoints">Endpoints</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#parameters">Parameters</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#examples">Examples</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#response-format">Response Format</a>
              </li>
            </ul>
          </div>
        </nav>

        <!-- Main Content -->
        <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-4">
          <div class="d-flex">
            <button id="themeToggle" class="btn border-0 fw-bold mt-3 ms-auto">
              <i id="themeIcon" class="bi"></i>
            </button>
          </div>
          <h1 class="mt-3 mb-4">API Documentation</h1>

          <p>This API provides access to a comprehensive database of images and their associated metadata. This documentation aims to guide you through the usage of our API, detailing the available endpoints, parameters, and the structure of responses. Whether you're integrating with our service or utilizing the data for analysis, this guide will provide you with all the necessary information to effectively utilize our API.</p>

          <hr>

          <h2 id="endpoints">Endpoints</h2>
          <p>Endpoints are specific paths in the API that you interact with to perform different actions. Below are the available endpoints for retrieving images and their details:</p>

          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Retrieve All Images</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?display=all_images</code>
              </p>
              <p>This endpoint allows you to retrieve a list of all images stored in the database. It's a comprehensive call that returns every image entry along with its metadata. This is useful for bulk retrieval or when you need to present a complete gallery of images.</p>
              <p><strong>Example Usage:</strong> Use this endpoint to load all images for a gallery view on a website or to generate a comprehensive image index.</p>
            </div>
          </div>

          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Retrieve Image by ID</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?artworkid={artworkid}</code>
              </p>
              <p>This endpoint retrieves detailed information about a specific image identified by its unique ID. This is particularly useful for fetching detailed metadata and content related to a single image.</p>
              <p><strong>Example Usage:</strong> When displaying a single image on a detail page, use this endpoint to fetch and show the image and its metadata, such as description, tags, and view count.</p>
            </div>
          </div>

          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Retrieve All Images by User ID</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?display=all_images&uid={uid}</code>
              </p>
              <p>This endpoint retrieves all images uploaded by a specific user, identified by their user ID. This is useful for displaying or managing images created or uploaded by a particular user.</p>
              <p><strong>Example Usage:</strong> Use this endpoint to create a user profile page that shows all images uploaded by the user, helping visitors view their contributions.</p>
            </div>
          </div>

          <hr>

          <h2 id="parameters">Parameters</h2>
          <p>Parameters are variables that you can pass in the API request to modify the output or filter the results. Below is a detailed explanation of each parameter you can use with the API:</p>

          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Common Parameters</h5>
              <ul>
                <li><strong>display</strong> (string): This parameter determines the type of response you will receive from the API. The available values include:
                  <ul>
                    <li><code>all_images</code>: Retrieves a complete list of all images.</li>
                    <li><code>info</code>: Retrieves detailed information about specific images or categories.</li>
                  </ul>
                </li>
                <li><strong>option</strong> (string): Specifies additional options for the response. Examples include:
                  <ul>
                    <li><code>image_child</code>: When specified, includes child images associated with parent images.</li>
                  </ul>
                </li>
                <li><strong>artwork_type</strong> (string): Filters images based on their type. Examples include:
                  <ul>
                    <li><code>illustration</code>: Filters to show only illustrations.</li>
                    <li><code>manga</code>: Filters to show only manga images.</li>
                  </ul>
                </li>
                <li><strong>type</strong> (string): Filters images based on their content type. Examples include:
                  <ul>
                    <li><code>safe</code>: Filters to show only images classified as safe.</li>
                    <li><code>nsfw</code>: Filters to show only images classified as not safe for work.</li>
                  </ul>
                </li>
                <li><strong>artworkid</strong> (integer): The unique identifier for an artwork. Use this to retrieve specific images.</li>
                <li><strong>uid</strong> (integer): The unique identifier for a user. Use this to filter images associated with a particular user.</li>
                <li><strong>character</strong> (string): Filters images by specific characters. Provide character names to filter the results. For example, <code>Character 1</code> will filter images featuring "Character 1".</li>
                <li><strong>parody</strong> (string): Filters images by parodies. Provide parody names to filter the results. For example, <code>Parody 1</code> will filter images related to "Parody 1".</li>
                <li><strong>tag</strong> (string): Filters images by tags. Provide tag names to filter the results. For example, <code>tag1</code> will filter images tagged with "tag1".</li>
                <li><strong>group</strong> (string): Filters images by groups. Provide group names to filter the results. For example, <code>Group 1</code> will filter images associated with "Group 1".</li>
                <li><strong>search</strong> (string): Searches for images based on a keyword. This parameter allows you to search for images that match the specified keyword in their title, description, or tags.</li>
              </ul>
            </div>
          </div>

          <hr>

          <h2 id="examples">Examples</h2>
          <p>Here are some detailed examples of how to use the API to retrieve various types of images. Each example includes the endpoint, parameters, and the expected result.</p>

          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Example 1: Retrieve All Images</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?display=all_images</code>
              </p>
              <p>This example demonstrates how to retrieve a list of all images available in the database. The response will include all image entries and their metadata.</p>
              <pre><code>
              {
                {
                  "images": [
                    {
                      "id": 1,
                      "title": "Image 1",
                      "description": "A description of Image 1",
                      "tags": ["tag1", "tag2"],
                      "url": "http://example.com/image1.jpg"
                    },
                    ...
                  ]
                }
              }
              </code></pre>
            </div>
          </div>

          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Example 2: Search for Images</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?search={keyword}</code>
              </p>
              <p>This example demonstrates how to search for images using a keyword. The API will return images that match the keyword in their title, description, or tags.</p>
              <pre><code>
              {
                {
                  "images": [
                    {
                      "id": 2,
                      "title": "Search Result Image",
                      "description": "A description of the search result image",
                      "tags": ["search", "result"],
                      "url": "http://example.com/search-result-image.jpg"
                    },
                    ...
                }
              }
              </code></pre>
            </div>
          </div>

          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Example 2: Retrieve All Safe Images with Child Images</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?display=all_images&option=image_child&type=safe</code>
              </p>
              <p>This request retrieves all images classified as safe and includes child images associated with those images. The <code>option</code> parameter is set to <code>image_child</code>, and the <code>type</code> parameter is set to <code>safe</code>.</p>
              <p><strong>Use Case:</strong> Use this endpoint when you need to display all safe images, including any child images, for content filtering or categorization purposes.</p>
              <pre><code>
              {
                {
                "request": "GET /api.php?display=all_images&option=image_child&type=safe",
                "description": "Retrieves all images classified as safe and includes child images.",
                "parameters": {
                  "display": "all_images",
                  "option": "image_child",
                  "type": "safe"
                }
              }
              </code></pre>
            </div>
          </div>
          
          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Example 3: Retrieve All Images by User ID</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?display=all_images&uid=1</code>
              </p>
              <p>This request retrieves all images associated with the user whose ID is 1. The <code>uid</code> parameter is used to filter the images by the specified user ID.</p>
              <p><strong>Use Case:</strong> This is useful for user profile pages where you want to show all images uploaded by a specific user.</p>
              <pre><code>
              {
                {
                "request": "GET /api.php?display=all_images&uid=1",
                "description": "Retrieves all images associated with the user whose ID is 1.",
                "parameters": {
                  "display": "all_images",
                  "uid": 1
                }
              }
              </code></pre>
            </div>
          </div>
          
          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Example 4: Retrieve a Specific Image by ID</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?artworkid=123</code>
              </p>
              <p>This request retrieves detailed information about the image with ID 123. The <code>artworkid</code> parameter specifies the unique ID of the image you want to retrieve.</p>
              <p><strong>Use Case:</strong> Use this endpoint when displaying a detailed view of a specific image, such as in a detail page or a modal.</p>
              <pre><code>
              {
                {
                "request": "GET /api.php?artworkid=123",
                "description": "Retrieves detailed information about the image with ID 123.",
                "parameters": {
                  "artworkid": 123
                }
              }
              </code></pre>
            </div>
          </div>
          
          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Example 5: Retrieve Images by Character</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?display=all_images&character=Character%201</code>
              </p>
              <p>This request retrieves all images that feature "Character 1". The <code>character</code> parameter is set to the name of the character you want to filter by.</p>
              <p><strong>Use Case:</strong> Useful for filtering images to show only those featuring a specific character, such as in a fan gallery or character-themed page.</p>
              <pre><code>
              {
                {
                "request": "GET /api.php?display=all_images&character=Character%201",
                "description": "Retrieves all images that feature 'Character 1'.",
                "parameters": {
                  "display": "all_images",
                  "character": "Character 1"
                }
              }
              </code></pre>
            </div>
          </div>
          
          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Example 6: Retrieve Images by Parody</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?display=all_images&parody=Parody%201</code>
              </p>
              <p>This request retrieves all images related to "Parody 1". The <code>parody</code> parameter filters the results to include only images associated with the specified parody.</p>
              <p><strong>Use Case:</strong> Ideal for showcasing all images related to a particular parody, which can be useful for thematic collections or galleries.</p>
              <pre><code>
              {
                {
                "request": "GET /api.php?display=all_images&parody=Parody%201",
                "description": "Retrieves all images related to 'Parody 1'.",
                "parameters": {
                  "display": "all_images",
                  "parody": "Parody 1"
                }
              }
              </code></pre>
            </div>
          </div>
          
          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Example 7: Retrieve Images by Tag</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?display=all_images&tag=tag1</code>
              </p>
              <p>This request retrieves all images that are tagged with "tag1". The <code>tag</code> parameter is used to filter images based on specific tags.</p>
              <p><strong>Use Case:</strong> Use this endpoint when you need to display images that share common tags, such as for thematic sorting or tag-based filtering.</p>
              <pre><code>
              {
                {
                "request": "GET /api.php?display=all_images&tag=tag1",
                "description": "Retrieves all images that are tagged with 'tag1'.",
                "parameters": {
                  "display": "all_images",
                  "tag": "tag1"
                }
              }
              </code></pre>
            </div>
          </div>
          
          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Example 8: Retrieve Images by Group</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?display=all_images&group=Group%201</code>
              </p>
              <p>This request retrieves all images that belong to "Group 1". The <code>group</code> parameter filters the results based on the group to which the images are associated.</p>
              <p><strong>Use Case:</strong> Useful for organizing and displaying images that belong to specific groups, such as collections or series.</p>
              <pre><code>
              {
                {
                "request": "GET /api.php?display=all_images&group=Group%201",
                "description": "Retrieves all images that belong to 'Group 1'.",
                "parameters": {
                  "display": "all_images",
                  "group": "Group 1"
                }
              }
              </code></pre>
            </div>
          </div>
          
          <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
            <div class="card-body">
              <h5 class="card-title">Example 9: Retrieve Images by Search</h5>
              <p class="card-text">
                <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/api.php?display=all_images&search=Search%201</code>
              </p>
              <p>This request retrieves all images that match the search term "Search 1". The <code>search</code> parameter filters the results based on the search query.</p>
              <p><strong>Use Case:</strong> Useful for organizing and displaying images based on search queries, such as title, tags, groups, characters, or parodies.</p>
              <pre><code>
              {
                {
                "request": "GET /api.php?display=all_images&search=Search%201",
                "description": "Retrieves all images that match the search term 'Search 1'.",
                "parameters": {
                  "display": "all_images",
                  "search": "Search 1"
                }
              }
              </code></pre>
            </div>
          </div>

          <hr>

          <h2 id="response-format">Response Format</h2>
          <p>The API returns data in JSON format. This standardized format makes it easy to parse and integrate into various applications. Below is an example of a JSON response for retrieving all images:</p>

          <pre><code>{
            "images": [
              {
                "id": 1,
                "filename": "image1.jpg",
                "tags": "tag1, tag2",
                "title": "Image Title 1",
                "imgdesc": "Description of image 1",
                "link": "http://example.com",
                "date": "2024-08-18",
                "view_count": 100,
                "type": "safe",
                "episode_name": "Episode 1",
                "artwork_type": "manga",
                "group": "Group 1",
                "categories": "category1, category2",
                "language": "English",
                "parodies": "Parody 1",
                "characters": "Character 1",
                "original_filename": "original_image1.jpg"
              },
              {
                "id": 2,
                "filename": "image2.jpg",
                "tags": "tag3, tag4",
                "title": "Image Title 2",
                "imgdesc": "Description of image 2",
                "link": "http://example.com",
                "date": "2024-08-19",
                "view_count": 150,
                "type": "nsfw",
                "episode_name": "Episode 2",
                "artwork_type": "illustration",
                "group": "Group 2",
                "categories": "category3, category4",
                "language": "Japanese",
                "parodies": "Parody 2",
                "characters": "Character 2",
                "original_filename": "original_image2.jpg"
              }
            ]
          }</code></pre>

          <p>In the response:</p>
          <ul>
            <li><strong>images</strong>: An array of image objects, each containing detailed metadata about the image.</li>
            <li><strong>id</strong>: The unique identifier for the image.</li>
            <li><strong>filename</strong>: The name of the image file.</li>
            <li><strong>tags</strong>: Comma-separated tags associated with the image.</li>
            <li><strong>title</strong>: The title of the image.</li>
            <li><strong>imgdesc</strong>: A description of the image.</li>
            <li><strong>link</strong>: A URL link related to the image.</li>
            <li><strong>date</strong>: The date when the image was added.</li>
            <li><strong>view_count</strong>: The number of times the image has been viewed.</li>
            <li><strong>type</strong>: The content type classification of the image (e.g., safe or nsfw).</li>
            <li><strong>episode_name</strong>: The name of the episode if the image is part of a series.</li>
            <li><strong>artwork_type</strong>: The type of artwork (e.g., manga, illustration).</li>
            <li><strong>group</strong>: The group or collection to which the image belongs.</li>
            <li><strong>categories</strong>: Categories assigned to the image.</li>
            <li><strong>language</strong>: The language in which the image's content is presented.</li>
            <li><strong>parodies</strong>: Any parodies associated with the image.</li>
            <li><strong>characters</strong>: Characters featured in the image.</li>
            <li><strong>original_filename</strong>: The original file name of the image.</li>
          </ul>

          <hr>

          <footer class="py-3 my-4 mb-5">
            <ul class="nav justify-content-center pb-3 mb-3 mt-4">
              <li class="nav-item fw-bold"><a href="https://twitter.com/" target="_blank" class="nav-link px-2 text-muted"><i class="bi bi-twitter" style="font-size: 18px;"></i> Twitter</a></li>
              <li class="nav-item fw-bold ms-3 me-3"><a href="https://github.com/HirotakaDango/ArtCODE" target="_blank" class="nav-link px-2 text-muted"><i class="bi bi-github" style="font-size: 18px;"></i> GitHub</a></li>
              <li class="nav-item fw-bold"><a href="https://gitlab.com/HirotakaDango/ArtCODE" target="_blank" class="nav-link px-2 text-muted"><i class="bi bi-gitlab" style="font-size: 18px;"></i> GitLab</a></li>
            </ul>
            <p class="text-center text-muted fw-bold">© 2022 - <?php echo date('Y'); ?>, ArtCODE Japan</p>
          </footer>
        </main>
      </div>
    </div>
    <script>
      // Get the theme toggle button, icon element, and html element
      const themeToggle = document.getElementById('themeToggle');
      const themeIcon = document.getElementById('themeIcon');
      const htmlElement = document.documentElement;

      // Check if the user's preference is stored in localStorage
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme) {
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateThemeIcon(savedTheme);
      }

      // Add an event listener to the theme toggle button
      themeToggle.addEventListener('click', () => {
        // Toggle the theme
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
        // Apply the new theme
        htmlElement.setAttribute('data-bs-theme', newTheme);
        updateThemeIcon(newTheme);

        // Store the user's preference in localStorage
        localStorage.setItem('theme', newTheme);
      });

      // Function to update the theme icon
      function updateThemeIcon(theme) {
        if (theme === 'dark') {
          themeIcon.classList.remove('bi-moon-fill');
          themeIcon.classList.add('bi-sun-fill');
        } else {
          themeIcon.classList.remove('bi-sun-fill');
          themeIcon.classList.add('bi-moon-fill');
        }
      }
    </script>
  </body>
</html>