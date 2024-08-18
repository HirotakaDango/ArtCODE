<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - How to Use</title>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('bootstrapcss.php'); ?>
  </head>
  <body>
    <div class="container mt-5">
      <h1 class="mb-4">API Documentation</h1>

      <p>This API provides access to images and their metadata. Below you'll find details on how to use the API, including available endpoints, parameters, and examples.</p>

      <hr>

      <h2>Endpoints</h2>
      <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
        <div class="card-body">
          <h5 class="card-title">Retrieve All Images</h5>
          <p class="card-text">
            <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/artwork_id_api.php?display=all_images</code>
          </p>
          <p>Returns all images in the database.</p>
        </div>
      </div>

      <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
        <div class="card-body">
          <h5 class="card-title">Retrieve Image by ID</h5>
          <p class="card-text">
            <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/artwork_id_api.php?artworkid=&#123;artworkid&#125;</code>
          </p>
          <p>Returns details about a specific image by its ID.</p>
        </div>
      </div>

      <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
        <div class="card-body">
          <h5 class="card-title">Retrieve All Images by User ID</h5>
          <p class="card-text">
            <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/artwork_id_api.php?display=all_images&uid=&#123;uid&#125;</code>
          </p>
          <p>Returns all images associated with the specified user ID.</p>
        </div>
      </div>

      <hr>

      <h2>Parameters</h2>
      <p>These parameters can be used to filter and modify the API responses:</p>

      <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
        <div class="card-body">
          <h5 class="card-title">Common Parameters</h5>
          <ul>
            <li><strong>display</strong> (string): Determines the type of response. Values: <code>all_images</code>, <code>info</code></li>
            <li><strong>option</strong> (string): Specifies additional options. E.g., <code>image_child</code> to include child images.</li>
            <li><strong>artwork_type</strong> (string): Filter images by type. E.g., <code>illustration</code>, <code>manga</code></li>
            <li><strong>type</strong> (string): Filter images by content type. E.g., <code>safe</code>, <code>nsfw</code></li>
            <li><strong>artworkid</strong> (integer): The unique ID of an artwork.</li>
            <li><strong>uid</strong> (integer): The unique ID of the user. Use this to filter images by user.</li>
          </ul>
        </div>
      </div>

      <hr>

      <h2>Examples</h2>

      <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
        <div class="card-body">
          <h5 class="card-title">Example 1: Retrieve All Manga Images</h5>
          <p class="card-text">
            <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/artwork_id_api.php?display=all_images&artwork_type=manga</code>
          </p>
          <p>Returns all images of type "manga".</p>
        </div>
      </div>

      <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
        <div class="card-body">
          <h5 class="card-title">Example 2: Retrieve All Safe Images with Child Images</h5>
          <p class="card-text">
            <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/artwork_id_api.php?display=all_images&option=image_child&type=safe</code>
          </p>
          <p>Returns all safe images, including their child images.</p>
        </div>
      </div>

      <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
        <div class="card-body">
          <h5 class="card-title">Example 3: Retrieve All Images by User ID</h5>
          <p class="card-text">
            <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/artwork_id_api.php?display=all_images&uid=1</code>
          </p>
          <p>Returns all images associated with the user whose ID is 1.</p>
        </div>
      </div>

      <div class="card rounded-4 bg-body-tertiary border-0 shadow mb-4">
        <div class="card-body">
          <h5 class="card-title">Example 4: Retrieve a Specific Image by ID</h5>
          <p class="card-text">
            <code>GET <?php echo $_SERVER['HTTP_HOST']; ?>/artwork_id_api.php?artworkid=123</code>
          </p>
          <p>Returns detailed information about the image with ID 123.</p>
        </div>
      </div>

      <hr>

      <h2>Response Format</h2>
      <p>The API returns responses in JSON format. Below is an example response for retrieving all images:</p>
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
          ...
        ]
      }</code></pre>
      <hr>

      <div>
        <footer class="py-3 my-4 mb-5">
          <ul class="nav justify-content-center pb-3 mb-3 mt-4">
            <li class="nav-item fw-bold"><a href="https://twitter.com/" target="_blank" class="nav-link px-2 text-muted"><i class="bi bi-twitter" style="font-size: 18px;"></i> Twitter</a></li>
            <li class="nav-item fw-bold ms-3 me-3"><a href="https://github.com/HirotakaDango/ArtCODE" target="_blank" class="nav-link px-2 text-muted"><i class="bi bi-github" style="font-size: 18px;"></i> GitHub</a></li>
            <li class="nav-item fw-bold"><a href="https://gitlab.com/HirotakaDango/ArtCODE" target="_blank" class="nav-link px-2 text-muted"><i class="bi bi-gitlab" style="font-size: 18px;"></i> GitLab</a></li>
          </ul>
          <p class="text-center text-muted fw-bold">© 2022 - <?php echo date('Y'); ?>, ArtCODE Japan</p>
        </footer>
      </div> 
    </div>
  </body>
</html>