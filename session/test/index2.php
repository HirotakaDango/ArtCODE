<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtCODE - Create, Share, Inspire</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>
    <?php include('../../bootstrapcss.php'); ?>
    <style>
      body {
        background-color: black;
      }

      #particles-js {
        position: fixed;
        width: 100%;
        height: 100%;
        z-index: -1;
      }

      footer {
        background-color: rgba(0, 0, 0, 0.8);
        color: #86868b;
        text-align: center;
        padding: 20px 0;
        font-size: 14px;
      }

      .social-links {
        margin-top: 20px;
      }

      .social-links a {
        color: #fff;
        text-decoration: none;
        margin: 0 10px;
        font-size: 16px;
      }

      .clickable-card {
        cursor: pointer;
        transition: box-shadow 0.8s ease, transform 0.8s ease;
      }

      .clickable-card:hover {
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        transform: translateY(-2px);
      }

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
        <div class="container text-center position-relative z-2">
          <h1 class="text-white py-2 fw-bold display-1">ArtCODE</h1>
          <h5 class="text-white py-2 fw-medium fs-1">Create, share, and inspire with unparalleled power and precision.</h5>
          <a href="login?tourl=<?php echo urlencode(isset($_GET['tourl']) ? $_GET['tourl'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/home/'); ?>" class="btn btn-light mt-4 fw-medium rounded-pill">Get Started <i class="bi bi-arrow-right" style="-webkit-text-stroke: 1px;"></i></a>
        </div>
        <img class="w-100 h-100 object-fit-cover position-absolute top-0 start-0 z-1" src="https://cdn.svgator.com/images/2021/10/solar-system-animation.svg">
      </section>
      <section id="features" class="section d-none">
        <div class="container fw-bold mt-5">
          <div class="row featurette">
            <div class="col-md-7">
              <h1 class="display-5 text-white fw-bold">Discover amazing artworks</h1>
              <p class="fs-5 text-white fw-medium">Explore our vast collection of beautiful artworks, created by talented artists from all over the world.</p>
              <p class="fs-5 text-white fw-medium">From digital art to traditional paintings, ArtCODE has it all.</p>
              <a href="preview_guest.php" class="btn fw-bold btn-light rounded-pill">Start Exploring</a>
            </div>
            <div class="col-md-5">
            </div>
          </div>
          <div class="row featurette mt-4">
            <div class="col-md-7 order-md-2">
              <h1 class="display-5 text-white fw-bold">Share your creativity</h1>
              <p class="fs-5 text-white fw-medium">Join our community and showcase your artwork to the world.</p>
              <p class="fs-5 text-white fw-medium">Connect with other artists, get feedback, and discover new opportunities.</p>
              <a href="register.php" class="btn fw-bold btn-light rounded-pill">Join Now</a>
            </div>
            <div class="col-md-5 order-md-1">
            </div>
          </div>
        </div>
      </section>
      <section id="advantages" class="section d-none">
        <div class="container px-4 py-5">
          <div class="row row-cols-1 row-cols-md-2 align-items-md-center g-5 py-5">
            <div class="col d-flex flex-column align-items-start gap-2">
              <h2 class="fw-bold text-white">Why ArtCODE? And the reason why choose ArtCODE?</h2>
              <p class="text-white fw-medium">ArtCODE is an online platform that combines the best features of Pixiv and Pinterest to create a unique and immersive experience for artists and art enthusiasts alike. Whether you're a seasoned professional or a budding beginner, ArtCODE provides a welcoming community where you can showcase your talent and connect with like-minded individuals from all over the world.</p>
              <a href="/upload" class="btn btn-light btn-lg rounded-pill fw-bold">Upload Your Works <i class="bi bi-arrow-right px-3" style="-webkit-text-stroke: 1px;"></i></a>
            </div>

            <div class="col">
              <div class="row row-cols-1 row-cols-sm-2 g-4">
                <div class="col d-flex flex-column gap-2">
                  <h4 class="fw-medium text-white mb-0">Artwork Galleries</h4>
                  <p class="text-white fw-medium">Browse and create stunning art galleries to showcase your artwork collections.</p>
                </div>

                <div class="col d-flex flex-column gap-2">
                  <h4 class="fw-medium text-white mb-0">Collaboration</h4>
                  <p class="text-white fw-medium">Collaborate with other artists and create stunning artworks together.</p>
                </div>

                <div class="col d-flex flex-column gap-2">
                  <h4 class="fw-medium text-white mb-0">Artistic Tools</h4>
                  <p class="text-white fw-medium">Access a wide range of artistic tools and resources to enhance your creativity.</p>
                </div>

                <div class="col d-flex flex-column gap-2">
                  <h4 class="fw-medium text-white mb-0">Global Community</h4>
                  <p class="text-white fw-medium">Connect with artists from around the world and be part of a thriving creative community.</p>
                </div>
              </div>
            </div>

          </div>
        </div>
      </section>
      <section class="section d-none">
        <div class="container">
          <div class="row">
            <div class="col-md-4 py-2">
              <div class="card h-100 clickable-card rounded-4 border-0 bg-dark text-white bg-opacity-25">
                <div class="card-body text-center">
                  <i class="bi bi-speedometer2 fs-1 mb-3 text-white"></i>
                  <h3 class="fw-bold text-start mt-3">Fast and Easy</h3>
                  <p class="text-start fw-medium">With ArtCODE, you can quickly and easily find the perfect artwork for your project or collection.</p>
                </div>
              </div>
            </div>
            <div class="col-md-4 py-2">
              <div class="card h-100 clickable-card rounded-4 border-0 bg-dark text-white bg-opacity-25">
                <div class="card-body text-center">
                  <i class="bi bi-globe fs-1 mb-3 text-white"></i>
                  <h3 class="fw-bold text-start mt-3">Global Community</h3>
                  <p class="text-start fw-medium">ArtCODE connects you with a global community of artists and art lovers, allowing you to discover new perspectives and inspiration from around the world.</p>
                </div>
              </div>
            </div>
            <div class="col-md-4 py-2">
              <div class="card h-100 clickable-card rounded-4 border-0 bg-dark text-white bg-opacity-25">
                <div class="card-body text-center">
                  <i class="bi bi-award fs-1 mb-3 text-white"></i>
                  <h3 class="fw-bold text-start mt-3">Quality and Diversity</h3>
                  <p class="text-start fw-medium">ArtCODE offers a diverse range of high-quality artworks, including traditional paintings, digital art, and more, to suit any taste and style.</p>
                </div>
              </div>
            </div>
          </div>
          <div class="row align-items-md-stretch py-3">
            <div class="col-md-6 mb-3 mb-md-0 mb-lg-0">
              <div class="h-100 p-5 border rounded-3 clickable-card rounded-4 border-0 bg-dark text-white bg-opacity-25">
                <h2 class="fw-bold">Best Platform To Share Your Ideas</h2>
                <p class="fw-medium mt-3">With our user-friendly interface, you can easily upload and organize your artwork into collections, share your creations with others, and discover new artists and artwork through our sophisticated recommendation system. You can also connect with other members of the community through comments and private messaging, participate in contests and events to showcase your work and win prizes, and gain exposure for your art through our social media channels.</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="h-100 p-5 border rounded-3 clickable-card rounded-4 border-0 bg-dark text-white bg-opacity-25">
                <h2 class="fw-bold">We're Supporting Your Artworks</h2>
                <p class="fw-medium mt-3">Our team is composed of passionate artists and developers who are dedicated to making ArtCODE the premier online destination for art lovers. We value creativity, diversity, and inclusivity, and we strive to create a platform that is accessible and enjoyable for everyone.</p>
              </div>
            </div>
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
            value: 1000,
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
    </script>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>