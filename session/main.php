        <section id="main">
          <div class="container-fluid px-md-5 py-md-2 py-lg-2">
            <div class="row align-items-center mt-md-0 mt-lg-0 pt-md-4 pt-lg-4">
              <div class="col-md-7 order-2 order-md-1">
                <h1 class="fw-bolder display-4 text-white animate__animated animate__fadeInLeft animate__delay-1s" style="max-width: 35.5rem;">Explore and unleash <span class='text-danger' style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">your creativity <a class="btn btn-outline-danger border-0 rounded-pill" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/login.php"><i class="bi bi-arrow-right display-5 px-3" style="-webkit-text-stroke: 3px;"></i></a></span></h1>
                <h6 class="pb-2 mb-md-4 mb-lg-5 text-white animate__animated animate__fadeInLeft animate__delay-1s">Unleash Your Creativity with Us! Join a vibrant community of innovators, artists, and visionaries. Sign up now to embark on an inspiring journey where your ideas come to life. Your creative adventure begins here!</h6>
                <div class="btn-group gap-2 mb-3 d-md-none d-lg-none">
                  <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/login.php?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/home/'); ?>" class="btn text-light-subtle border-0 link-body-emphasis fw-bold me-2"><i class="bi bi-person-fill-up"></i> sign in</a>
                  <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/register.php?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/home/'); ?>" class="btn rounded-pill <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'btn-outline-light' : 'btn-outline-dark'; ?> fw-bold"><i class="bi bi-person-plus-fill"></i> sign up for free</a>
                </div>
              </div>
              <div class="col-md-5 order-1 order-md-2 mb-3 mb-md-0 animate__animated animate__fadeInRight animate__delay-1s">
                <div class="parallax mx-auto" style="max-width: 526px;">
                  <div class="parallax-layer position-relative" data-depth="0.1"><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/contents/layer01.svg" alt="Layer"></div>
                  <div class="parallax-layer" data-depth="0.16"><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/contents/layer02.svg" alt="Layer"></div>
                  <div class="parallax-layer" data-depth="0.38"><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/contents/layer03.svg" alt="Layer"></div>
                  <div class="parallax-layer" data-depth="0.16"><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/contents/layer04.svg" alt="Layer"></div>
                  <div class="parallax-layer" data-depth="0.16"><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/contents/layer05.svg" alt="Layer"></div>
                  <div class="parallax-layer" data-depth="0.45"><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/contents/layer06.svg" alt="Layer"></div>
                  <div class="parallax-layer" data-depth="0.3"><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/contents/layer07.svg" alt="Layer"></div>
                  <div class="parallax-layer" data-depth="0.2"><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/session/contents/layer08.svg" alt="Layer"></div>
                </div>
              </div>
            </div>
            <div class="mt-4 mt-md-0 mt-lg-0 d-none d-md-block d-lg-block animate__animated animate__fadeInUp animate__delay-2s">
              <h3 class="fw-bold">Popular Tags</h3>
              <?php
                // Open the SQLite database
                $db = new SQLite3('../database.sqlite');

                // SQL query to get the most popular tags and their counts
                $query = "SELECT SUBSTR(tags, 1, INSTR(tags, ',') - 1) as first_tag, COUNT(*) as tag_count FROM images WHERE tags LIKE '%,%' GROUP BY first_tag ORDER BY tag_count DESC LIMIT 7";

                $result = $db->query($query);
                
                if ($result) {
                  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $firstTag = $row['first_tag'];
                    $tagCount = $row['tag_count'];

                    // Display each first tag as an <a> tag with the total count
                    echo "<a class='clickable-card fw-medium btn btn-dark bg-dark rounded-pill bg-opacity-25 m-1' href='../tagged_images.php?tag=". $firstTag ."'><i class='bi bi-tags-fill'></i> $firstTag</a>";
                  }

                  // Close the database connection
                  $db->close();
                } else {
                  echo "Error executing query: " . $db->lastErrorMsg();
                }
              ?>
            </div>
            <div class="container-fluid rounded-pill bg-dark bg-opacity-25 p-2 mt-1 d-none d-md-block d-lg-block animate__animated animate__fadeInUp animate__delay-3s">
              <form class="d-flex" role="search" action="../search.php" method="GET">
                <input class="form-control fw-medium me-2 border-0 rounded-start-5 bg-dark bg-opacity-50 focus-ring focus-ring-dark animate__animated animate__fadeInLeft animate__delay-4s" name="search" type="search" placeholder="Search tags or titles..." aria-label="Search">
                <div class="border-end border-start border-2 animate__animated animate__fadeInBottom animate__delay-4s"></div>
                <button class="btn ms-2 border-0 rounded-end-5 bg-dark bg-opacity-50 animate__animated animate__fadeInRight animate__delay-4s" type="submit"><i class="bi bi-search" style="-webkit-text-stroke: 2px;"></i></button>
              </form>
            </div>
          </div>
        </section>