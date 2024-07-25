          <link rel="stylesheet" href="/admin/admin_header.css">
          <div class="flex-shrink-0 p-3 px-4 bg-dark-subtle vh-100 overflow-auto">
            <a href="/" class="d-flex justify-content-center align-items-center pb-2 mb-3 link-body-emphasis text-decoration-none border-bottom text-center">
              <span class="fs-5 fw-bold text-center">Menu</span>
            </a>
            <ul class="list-unstyled ps-0">
              <li class="mb-1">
                <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed" data-bs-toggle="collapse" data-bs-target="#home-collapse" aria-expanded="true">
                  Dashboard
                </button>
                <div class="collapse show" id="home-collapse">
                  <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                    <li><a href="/admin/analytic/" class="link-body-emphasis d-inline-flex text-decoration-none rounded">General</a></li>
                    <li><a href="/admin/analytic/#contentSection" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Contents</a></li>
                    <li><a href="/admin/analytic/#regionSection" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Regions</a></li>
                    <li><a href="/admin/analytic/#mediaSection" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Media</a></li>
                    <li><a href="/admin/analytic/#activitySection" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Activity</a></li>
                    <li><a href="/admin/analytic/#analysisSection" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Analysis</a></li>
                  </ul>
                </div>
              </li>
              <li class="mb-1">
                <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed" data-bs-toggle="collapse" data-bs-target="#news-collapse" aria-expanded="true">
                  News
                </button>
                <div class="collapse show" id="news-collapse">
                  <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                    <li><a href="/admin/news/" class="link-body-emphasis d-inline-flex text-decoration-none rounded">All</a></li>
                    <li><a href="/admin/news/upload/" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Upload</a></li>
                  </ul>
                </div>
              </li>
              <li class="mb-1">
                <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed" data-bs-toggle="collapse" data-bs-target="#images-collapse" aria-expanded="true">
                  Images
                </button>
                <div class="collapse show" id="images-collapse">
                  <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                    <li><a href="/admin/images_section/" class="link-body-emphasis d-inline-flex text-decoration-none rounded">All</a></li>
                    <li><a href="#" class="link-body-emphasis d-inline-flex text-decoration-none rounded" data-bs-toggle="modal" data-bs-target="#searchModal">Search</a></li>
                    <li><a href="/admin/images_section/keywords/tags/" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Tags</a></li>
                    <li><a href="/admin/images_section/keywords/groups/" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Groups</a></li>
                    <li><a href="/admin/images_section/keywords/parodies/" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Parodies</a></li>
                  </ul>
                </div>
              </li>
              <li class="border-top my-3"></li>
              <li class="mb-1">
                <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed" data-bs-toggle="collapse" data-bs-target="#account-collapse" aria-expanded="false">
                  Account
                </button>
                <div class="collapse" id="account-collapse">
                  <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                    <li><a href="/admin/profile/" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Profile</a></li>
                    <li><a href="/admin/settings/" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Settings</a></li>
                    <li><a href="/admin/logout.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Sign out</a></li>
                  </ul>
                </div>
              </li>
            </ul>
          </div>
          <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content bg-transparent border-0">
                <div class="modal-body">
                  <form class="input-group" role="search" action="/admin/images_section/search/">
                    <input class="form-control rounded-start-4 border-0 bg-body-tertiary focus-ring focus-ring-dark" name="q" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn rounded-end-4 border-0 bg-body-tertiary" type="submit"><i class="bi bi-search"></i></button>
                  </form>
                </div>
              </div>
            </div>
          </div>