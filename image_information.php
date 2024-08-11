              <div class="container-fluid bg-body-tertiary p-2 mt-2 mb-2 rounded-4 text-center align-items-center d-flex justify-content-center">
                <div class="dropdown-center">
                  <button class="btn border-0 fw-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <small>
                      <?php echo date('Y/m/d', strtotime($image['date'])); ?>
                    </small
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-medium text-center" href="#">
                        uploaded at <?php echo date('F j, Y', strtotime($image['date'])); ?>
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="dropdown-center">
                  <button class="btn border-0 fw-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-heart-fill text-sm"></i> <small><?php echo $fav_count; ?></small>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-medium text-center" href="#">
                        total <?php echo $fav_count; ?> favorites
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="dropdown-center">
                  <button class="btn border-0 fw-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-eye-fill"></i> <small><?php echo $viewCount; ?></small>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item fw-medium text-center" href="#">
                        total <?php echo $viewCount; ?> views
                      </a>
                    </li>
                  </ul>
                </div>
              </div>