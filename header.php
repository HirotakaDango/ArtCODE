<?php
// Connect to the SQLite database
$db1 = new SQLite3('database.sqlite');

// Get the artist name from the database
$email1 = $_SESSION['email'];
$stmt1 = $db1->prepare("SELECT id, artist, pic FROM users WHERE email = :email");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();
$row1 = $result1->fetchArray();
$pic1 = $row1['pic'];
$artist1 = $row1['artist'];
$user_id1 = $row1['id'];

// Count the number of followers
$stmt1 = $db1->prepare("SELECT COUNT(*) AS num_followers FROM following WHERE following_email = :email");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();
$row1 = $result1->fetchArray();
$num_followers1 = $row1['num_followers'];

// Count the number of following
$stmt1 = $db1->prepare("SELECT COUNT(*) AS num_following FROM following WHERE follower_email = :email");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();
$row1 = $result1->fetchArray();
$num_following1 = $row1['num_following'];

// Get all of the images uploaded by the current user
$stmt1 = $db1->prepare("SELECT * FROM images WHERE email = :email ORDER BY id DESC");
$stmt1->bindValue(':email', $email1);
$result1 = $stmt1->execute();

// Count the number of images uploaded by the current user
$count1 = 0;
while ($image1 = $result1->fetchArray()) {
  $count1++;
}
  
$fav_result1 = $db1->query("SELECT COUNT(*) FROM favorites WHERE email = '{$_SESSION['email']}'");
$fav_count1 = $fav_result1->fetchArray()[0];
?>

    <!-- Navbar -->
    <nav class="navbar fixed-top navbar-expand-md navbar-expand-lg navbar-light bg-body-tertiary">
      <div class="container-fluid">
        <button class="navbar-toggler1 d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
          <img src="icon/toggle1.svg" width="22" height="22">
        </button> 
        <a class="navbar-brand text-secondary fw-bold" href="index.php">
          ArtCODE
        </a>
        <div class="dropdown nav-right">
          <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false">
            <img class="rounded-circle object-fit-cover border border-1" width="32" height="32" src="<?php echo !empty($pic1) ? $pic1 : "icon/profile.svg"; ?>" alt="Profile Picture" style="margin-top: -2px;">
          </a>
          <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
            <div class="text-center mb-2">
              <a class="d-block" href="settings/profile_picture.php"><img class="rounded-circle object-fit-cover border border-5" width="150" height="150" src="<?php echo !empty($pic1) ? $pic1 : "icon/profile.svg"; ?>" alt="Profile Picture"></a>
              <h5 class="fw-bold mt-2 "><?php echo $artist1; ?></h5>
              <p class="text-secondary fw-bold" style="margin-top: -12px;"><small><?php echo $email1; ?></small></p>
            </div>
            <div class="btn-group mt-2 mb-1 container" role="group" aria-label="Basic example">
              <a class="btn btn-sm btn-outline-secondary rounded fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'follower.php') echo 'active' ?>" href="follower.php?id=<?php echo $user_id1; ?>"><i class="bi bi-people-fill"></i> <?php echo $num_followers1 ?> <small>followers</small></a>
              <a class="btn btn-sm btn-outline-secondary ms-1 rounded fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'following.php') echo 'active' ?>" href="following.php?id=<?php echo $user_id1; ?>"><i class="bi bi-person-fill"></i> <?php echo $num_following1 ?> <small>following</small></a>
            </div>
            <div class="btn-group mb-3 container" role="group" aria-label="Basic example">
              <a class="btn btn-sm btn-outline-secondary rounded fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'myworks.php') echo 'active' ?>" href="myworks.php"><i class="bi bi-images"></i> <?php echo $count1; ?> <small>images</small></a>
              <a class="btn btn-sm btn-outline-secondary ms-1 rounded fw-bold <?php if(basename($_SERVER['PHP_SELF']) == 'favorite.php') echo 'active' ?>" href="favorite.php"><i class="bi bi-heart-fill"></i> <?php echo $fav_count1;?> <small>favorites</small></a> 
            </div>
            <div class="ms-1 me-1">
              <li>
                <a class="dropdown-item hover-effect fw-bold mb-1 <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'text-white bg-sec rounded' : 'text-s'; ?>" href="profile.php">
                  Profile
                </a>
              </li>
              <li>
                <a class="dropdown-item hover-effect fw-bold mb-1 <?php echo (basename($_SERVER['PHP_SELF']) == 'myworks.php') ? 'text-white bg-sec rounded' : 'text-s'; ?>" href="myworks.php">
                  My Works
                </a>
              </li>
              <li>
                <a class="dropdown-item hover-effect fw-bold mb-1 <?php echo (basename($_SERVER['PHP_SELF']) == 'album.php') ? 'text-white bg-sec rounded' : 'text-s'; ?>" href="album.php">
                  My Albums
                </a>
              </li>
              <li>
                <a class="dropdown-item hover-effect fw-bold mb-1 <?php echo (basename($_SERVER['PHP_SELF']) == 'history.php') ? 'text-white bg-sec rounded' : 'text-s'; ?>" href="history.php">
                  History
                </a>
              </li>
              <li>
                <a class="dropdown-item hover-effect fw-bold mb-1 <?php echo (basename($_SERVER['PHP_SELF']) == 'setting.php') ? 'text-white bg-sec rounded' : 'text-s'; ?>" href="setting.php">
                  Settings
                </a>
              </li>
              <hr class="border-3 rounded">
              <?php if(isset($_SESSION['email']) && isset($_COOKIE['token'])): ?>
                <li>
                  <a class="btn btn-danger fw-bold w-100" href="#" data-bs-toggle="modal" data-bs-target="#logOut">
                    Logout
                  </a>
                </li>
              <?php else: ?>
                <li>
                  <a class="btn btn-primary fw-bold w-100 <?php echo (basename($_SERVER['PHP_SELF']) == 'session.php'); ?>" href="session.php">
                    Signin
                  </a>
                </li>
              <?php endif; ?> 
            </div>
          </ul>
        </div> 
        <div class="offcanvas offcanvas-start" tabindex="-1" id="navbar" aria-labelledby="navbarLabel">
          <div class="offcanvas-header">
            <h5 class="offcanvas-title text-secondary" id="navbarLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <!-- Mobile -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fw-bold d-none-sm">
              <form action="search.php" method="GET" class="mb-3">
                <div class="input-group">
                  <input type="text" name="search" class="form-control fw-bold" placeholder="Search tags or title (e.g: white, sky)" required onkeyup="debouncedShowSuggestions(this, 'suggestions1')" />
                  <button type="submit" class="btn btn-primary"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
                </div>
                <div id="suggestions1"></div>
              </form> 
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active' ?>" href="index.php">
                  <i class="bi bi-house-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Home</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'forum.php') echo 'active' ?>" href="forum.php">
                  <i class="bi bi-chat-left-dots-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Forum</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'imgupload.php') echo 'active' ?>" href="imgupload.php">
                  <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Uploads</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'popular.php') echo 'active' ?>" href="popular.php">
                  <i class="bi bi-star-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Popular</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'notification.php') echo 'active' ?>" href="notification.php">
                  <i class="bi bi-bell-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Notification</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'status.php') echo 'active' ?>" href="status.php">
                  <i class="bi bi-card-text fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Status</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'active' ?>" href="tags.php">
                  <i class="bi bi-tags-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Tags</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'users.php') echo 'active' ?>" href="users.php">
                  <i class="bi bi-people-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Users</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'global.php') echo 'active' ?>" href="global.php">
                  <i class="bi bi-compass-fill fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Explore</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'news.php') echo 'active' ?>" href="news.php">
                  <i class="bi bi-newspaper fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Update & News</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'support.php') echo 'active' ?>" href="support.php">
                  <i class="bi bi-headset fs-5"></i>
                  <span class="d-md-none d-lg-inline d-lg-none ms-2">Support</span>
                </a>
              </li>
            </ul>
            <!-- end -->
            
            <!-- Desktop -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 fw-bold d-none-md-lg">
              <li class="nav-item">
                <a class="fw-semibold nav-center btn btn-smaller btn-outline-secondary rounded-pill text-nowrap <?php if(basename($_SERVER['PHP_SELF']) == 'imgupload.php') echo 'active text-white' ?>" href="imgupload.php">
                  <i class="bi bi-cloud-arrow-up-fill fs-5"></i> uploads
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active' ?>" href="index.php">
                  Home
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'forum.php') echo 'active' ?>" href="forum.php">
                  Forum
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'popular.php') echo 'active' ?>" href="popular.php">
                  Popular
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'search.php') echo 'active' ?>" href="#" data-bs-toggle="modal" data-bs-target="#searchTerm">
                  Search
                </a>
              </li>
              <li class="nav-item">
                <div class="dropdown-center">
                  <a class="btn btn-sm" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-grid-3x3-gap-fill fs-5 text-secondary"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end" style="width: 200px;">
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'notification.php') echo 'active' ?>" href="notification.php">
                        <i class="bi bi-card-text fs-5"></i>
                        <span class="d-lg-inline ms-2">Notification</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'status.php') echo 'active' ?>" href="status.php">
                        <i class="bi bi-card-text fs-5"></i>
                        <span class="d-lg-inline ms-2">Status</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'tags.php') echo 'active' ?>" href="tags.php">
                        <i class="bi bi-tags-fill fs-5"></i>
                        <span class="d-lg-inline ms-2">Tags</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'users.php') echo 'active' ?>" href="users.php">
                        <i class="bi bi-people-fill fs-5"></i>
                        <span class="d-lg-inline ms-2">Users</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'global.php') echo 'active' ?>" href="global.php">
                        <i class="bi bi-compass-fill fs-5"></i>
                        <span class="d-lg-inline ms-2">Explore</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'news.php') echo 'active' ?>" href="news.php">
                        <i class="bi bi-newspaper fs-5"></i>
                        <span class="d-lg-inline ms-2">Update & News</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link nav-center <?php if(basename($_SERVER['PHP_SELF']) == 'support.php') echo 'active' ?>" href="support.php">
                        <i class="bi bi-headset fs-5"></i>
                        <span class="d-lg-inline ms-2">Support</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
            </ul>
            <!-- end -->
          </div>
        </div>
      </div>
    </nav>
    <br><br>
    <!-- Modal -->
    <div class="modal fade" id="logOut" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content rounded-3 shadow">
          <div class="modal-body p-4 text-center">
            <h5 class="mb-0">Do you want to end the session?</h5>
            <p class="mb-0 mt-2">You can always comeback whenever you want later.</p>
          </div>
          <div class="modal-footer flex-nowrap p-0">
            <a class="btn btn-lg btn-link text-danger fs-6 text-decoration-none col-6 py-3 m-0 rounded-0 border-end" href="logout.php"><strong>Yes, end the session!</strong></a>
            <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0" data-bs-dismiss="modal">Cancel, keep it!</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="searchTerm" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Search</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="search.php" method="GET" class="mb-3">
              <div class="input-group">
                <input type="text" name="search" class="form-control fw-bold" placeholder="Search tags or title" required onkeyup="debouncedShowSuggestions(this, 'suggestions2')" />
                <button type="submit" class="btn btn-primary"><i class="bi bi-search" style="-webkit-text-stroke: 1px;"></i></button>
              </div>
              <div id="suggestions2"></div>
            </form>
            <h5 class="fw-bold text-center">Search Tips</h5>
            <p class="fw-semibold text-center">"You can search multi tags or title using comma to get multiple result!"</p>
            <p class="fw-semibold">example:</p>
            <input class="form-control text-dark fw-bold" placeholder="tags, title (e.g: white, sky)" readonly>
          </div>
        </div>
      </div>
    </div> 
    <style>
      .hover-effect:hover {
        color: white;
        background-color: #6c757d;
        border-radius: 5px;
      }
      
      .text-s {
        color: #6c757d;
      }
      
      .bg-sec {
        background-color: #6c757d;
      }
      
      @media (min-width: 768px) {
        .navbar-nav {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
        }
      
        .nav-center {
          margin-left: 15px;
          margin-right: 15px;
        }
      
        .nav-right {
          position: absolute;
          right: 10px;
          top: 10;
          align-items: center;
        }
        
        .d-none-sm {
          display: none;
        }
      }
      
      @media (max-width: 767px) {
        .d-none-md-lg {
          display: none;
        }
        .navbar-brand {
          position: static;
          display: block;
          text-align: center;
          margin: auto;
          transform: none;
        }

        .navbar-brand {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          font-size: 18px;
        }
      }
      
      .btn-smaller {
        padding: 2px 4px;
      }
      
      .navbar {
        height: 45px;
      }
      
      .navbar-brand {
        font-size: 18px;
      }

      @media (min-width: 992px) {
        .navbar-toggler1 {
          display: none;
        }
      }
    
      .navbar-toggler1 {
        background-color: #ededed;
        border: none;
        font-size: 8px;
        margin-top: -2px;
        margin-left: 8px;
        border-radius: 5px;
        padding: 6px;
        transition: background-color 0.3s ease; 
      }

      .navbar-toggler1:hover {
        background-color: rgba(0,0,0,0.2);
      } 
    </style>
    <script>
      var suggestedTags = {};

      function debounce(func, wait) {
        let timeout;
        return function (...args) {
          clearTimeout(timeout);
          timeout = setTimeout(() => {
            func.apply(this, args);
          }, wait);
        };
      }

      function showSuggestions(input, suggestionsId) {
        // Get the suggestions element
        var suggestionsElement = document.getElementById(suggestionsId);

        // Clear previous suggestions
        suggestionsElement.innerHTML = "";

        // If the input is empty, hide the suggestions
        var inputValue = input.value.trim();
        if (inputValue === "") {
          return;
        }

        // Fetch suggestions from the server using AJAX
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
          if (this.readyState === 4 && this.status === 200) {
            var suggestions = JSON.parse(this.responseText);

            // Create a dropdown for suggestions using Bootstrap classes
            var dropdownDiv = document.createElement("div");
            dropdownDiv.classList.add("dropdown-menu", "show");

            // Clear the suggestedTags array before adding new suggestions
            suggestedTags[suggestionsId] = [];

            suggestions.forEach(function (suggestion) {
              // Check if the suggestion is not already in the suggestedTags array
              if (!suggestedTags[suggestionsId].includes(suggestion)) {
                suggestedTags[suggestionsId].push(suggestion);

                var a = document.createElement("a");
                a.classList.add("dropdown-item");
                a.href = "#";
                a.textContent = suggestion;
                a.onclick = function () {
                  addTag(input, suggestionsId, suggestion);
                };
                dropdownDiv.appendChild(a);
              }
            });

            // Append the dropdown to the suggestions element
            suggestionsElement.appendChild(dropdownDiv);
          }
        };
        xhttp.open("GET", "get_suggestions.php?q=" + inputValue, true);
        xhttp.send();
      }

      var debouncedShowSuggestions = debounce(showSuggestions, 300);
  
      function addTag(input, suggestionsId, tag) {
        // Get the current input value
        var currentValue = input.value.trim();

        // If the current input value is empty, set the clicked suggestion as the input value
        if (currentValue === "") {
          input.value = tag;
        } else {
          // Otherwise, add the clicked suggestion as a new tag
          var tags = currentValue.split(",").map(function (item) {
            return item.trim();
          });

          // Check if the tag is not already in the tags list
          if (!tags.includes(tag)) {
            // Check if the tag starts with the current input prefix
            var prefix = tags[tags.length - 1];
            if (tag.toLowerCase().startsWith(prefix.toLowerCase())) {
              // Remove the prefix from the new tag to avoid duplication
              var newTag = tag.slice(prefix.length).trim();

              // If there is a comma at the end of the prefix, remove it
              if (tags[tags.length - 1].endsWith(",")) {
                tags[tags.length - 1] = tags[tags.length - 1].slice(0, -1).trim();
              }

              // Add the new tag to the list without any whitespace
              tags[tags.length - 1] = tags[tags.length - 1] + newTag;
            } else {
              tags.push(tag);
            }

            input.value = tags.join(", ");
          }
        }

        // Clear the suggestions
        var suggestionsElement = document.getElementById(suggestionsId);
        suggestionsElement.innerHTML = "";
      }
    </script>
