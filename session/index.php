<?php
// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite'); 
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, password TEXT, artist TEXT, pic TEXT, desc TEXT, bgpic TEXT, token TEXT, twitter TEXT, pixiv TEXT, other, region TEXT, joined DATETIME, born DATETIME, numpage TEXT, display TEXT, message_1 TEXT, message_2 TEXT, message_3 TEXT, message_4 TEXT, mode TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS news (id INTEGER PRIMARY KEY, title TEXT, description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, ver TEXT, verlink TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS images (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, email TEXT, tags TEXT, title TEXT, imgdesc TEXT, link TEXT, date DATETIME, view_count INT DEFAULT 0, type TEXT, episode_name TEXT, artwork_type TEXT, `group` TEXT, categories TEXT, language TEXT, parodies TEXT, characters TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS image_child (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT NOT NULL, image_id INTEGER NOT NULL, email TEXT NOT NULL, FOREIGN KEY (image_id) REFERENCES images (id))");
$stmt->execute();

// Create the "visit" table if it doesn't exist
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS visit (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  visit_count INTEGER,
  visit_date DATE DEFAULT CURRENT_DATE,
  UNIQUE(visit_date)
)");
$stmt->execute();

// Process any visit requests
$stmt = $db->prepare("SELECT id, visit_count FROM visit WHERE visit_date = CURRENT_DATE");
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if ($row) {
  // If the record for the current date exists, increment the visit_count
  $visitCount = $row['visit_count'] + 1;
  $stmt = $db->prepare("UPDATE visit SET visit_count = :visitCount WHERE id = :id");
  $stmt->bindValue(':visitCount', $visitCount, SQLITE3_INTEGER);
  $stmt->bindValue(':id', $row['id'], SQLITE3_INTEGER);
  $stmt->execute();
} else {
  // If the record for the current date doesn't exist, insert a new record
  $stmt = $db->prepare("INSERT INTO visit (visit_count) VALUES (:visitCount)");
  $stmt->bindValue(':visitCount', 1, SQLITE3_INTEGER);
  $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtCODE - Create, Share, Inspire</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>
    <link rel="icon" type="image/png" href="/icon/favicon.png">
    <?php include('../bootstrapcss.php'); ?>
    <style>
      .page-loading {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        -webkit-transition: all .4s .2s ease-in-out;
        transition: all .4s .2s ease-in-out;
        background-color: #000;
        opacity: 0;
        visibility: hidden;
        z-index: 9999;
      }
      
      .page-loading.active {
        opacity: 1;
        visibility: visible;
      }
      
      .page-loading-inner {
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        text-align: center;
        -webkit-transform: translateY(-50%);
        transform: translateY(-50%);
        -webkit-transition: opacity .2s ease-in-out;
        transition: opacity .2s ease-in-out;
        opacity: 0;
      }
      
      .page-loading.active > .page-loading-inner {
        opacity: 1;
      }

      .page-loading-inner > span {
        display: block;
        font-size: 1rem;
        font-weight: normal;
        color: #666276;;
      }
      
      .page-spinner {
        display: inline-block;
        width: 0.75rem;
        height: 0.75rem;
        margin-bottom: .75rem;
        vertical-align: text-bottom;
        border: 2.55em solid #bbb7c5;
        border-right-color: transparent;
        border-radius: 50%;
        -webkit-animation: spinner .75s linear infinite;
        animation: spinner .75s linear infinite;
      }
      
      @-webkit-keyframes spinner {
        100% {
          -webkit-transform: rotate(360deg);
          transform: rotate(360deg);
        }
      }
      
      @keyframes spinner {
        100% {
          -webkit-transform: rotate(360deg);
          transform: rotate(360deg);
        }
      }

      #particles-js {
        position: fixed;
        width: 100%;
        height: 100%;
        z-index: 0;
      }
 
       .typing-text::after {
        content: '|';
        animation: blink 0.7s infinite;
      }
      
      @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0; }
      }

      body { background-color: black; }

      .radiating-light {
        position: relative;
        color: #fff;
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.5), 0 0 20px rgba(255, 255, 255, 0.3), 0 0 30px rgba(255, 255, 255, 0.2);
      }
    </style>
  </head>
  <body>
    <div class="page-loading active">
      <div class="page-loading-inner">
        <div class="page-spinner"></div><span class="fw-bold">Loading...</span>
      </div>
    </div>
    <div id="particles-js"></div>
    <div class="container z-2">
      <section id="home" class="d-flex justify-content-center align-items-center vh-100 posit">
        <div class="container text-center z-2">
          <h1 class="text-white py-2 fw-bold display-1 radiating-light">ArtCODE</h1>
          <h5 class="text-white py-2 fw-medium fs-1 typing-text radiating-light"></h5>
          <a href="/session/login?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/home/'); ?>" class="btn btn-light mt-4 fw-bold rounded-pill">Get Started <i class="bi bi-arrow-right" style="-webkit-text-stroke: 1px;"></i></a>
          <div class="btn-group position-fixed bottom-0 start-0 m-2">
            <h6 class="fw-medium text-white">© 2022 - <?php echo date('Y'); ?> ArtCODE</h6>
          </div>
          <div class="btn-group position-fixed bottom-0 end-0 m-2">
            <a class="btn border-0" href="#"><i class="bi bi-newspaper text-white"></i></a>
            <a class="btn border-0" href="https://github.com/HirotakaDango/ArtCODE"><i class="bi bi-github text-white"></i></a>
            <a class="btn border-0" href="https://gitlab.com/HirotakaDango/ArtCODE"><i class="bi bi-gitlab text-white"></i></a>
            <a class="btn border-0" href="https://x.com/r89dango"><i class="bi bi-twitter-x text-white"></i></a>
          </div>
        </div>
      </section>
    </div>
    <script>
      gsap.registerPlugin(ScrollTrigger);
      const sections = document.querySelectorAll('.section');
      sections.forEach((section) => {
        gsap.fromTo(section, {
          opacity: 0,
          y: 50
        }, {
          opacity: 1,
          y: 0,
          duration: 1,
          scrollTrigger: {
            trigger: section,
            start: 'top 80%',
            end: 'bottom 20%',
            toggleActions: 'play none none reverse'
          }
        });
      });
      // Particles.js configuration
      particlesJS('particles-js', {
        particles: {
          number: {
            value: 150,
            density: {
              enable: true,
              value_area: 800
            }
          },
          color: {
            value: "#ffffff"
          },
          shape: {
            type: "circle"
          },
          opacity: {
            value: 0.5,
            random: true,
            anim: {
              enable: true,
              speed: 1,
              opacity_min: 0.1,
              sync: false
            }
          },
          size: {
            value: 3,
            random: true,
            anim: {
              enable: false,
              speed: 4,
              size_min: 0.3,
              sync: false
            }
          },
          line_linked: {
            enable: false,
            distance: 150,
            color: "#ffffff",
            opacity: 0.4,
            width: 1
          },
          move: {
            enable: true,
            speed: 1,
            direction: "none",
            random: true,
            straight: false,
            out_mode: "out",
            bounce: false,
            attract: {
              enable: false,
              rotateX: 600,
              rotateY: 1200
            }
          }
        },
        interactivity: {
          detect_on: "canvas",
          events: {
            onhover: {
              enable: true,
              mode: "repulse"
            },
            onclick: {
              enable: true,
              mode: "push"
            },
            resize: true
          },
          modes: {
            repulse: {
              distance: 100,
              duration: 0.4
            },
            push: {
              particles_nb: 4
            }
          }
        },
        retina_detect: true
      });

      (function () {
        window.onload = function () {
          var preloader = document.querySelector('.page-loading');
          preloader.classList.remove('active');
          setTimeout(function () {
            preloader.remove();
          }, 2000);
        };
      })();

      const typingText = document.querySelector('.typing-text');
      const text = "Create, share, and upload your artworks and connect to fellow artists around the world.";
      let index = 0;
      let isDeleting = false;
    
      function typeLoop() {
        const currentText = text.substring(0, index);
        typingText.textContent = currentText;
    
        if (!isDeleting && index < text.length) {
          index++;
          setTimeout(typeLoop, 50);
        } else if (isDeleting && index > 0) {
          index--;
          setTimeout(typeLoop, 25);
        } else {
          isDeleting = !isDeleting;
          setTimeout(typeLoop, isDeleting ? 1000 : 2000);
        }
      }
    
      window.addEventListener('load', typeLoop);

      // Start typing effect after page load
      window.addEventListener('load', () => {
        setTimeout(typeText, 1000); // Delay before starting the typing effect
      });
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>