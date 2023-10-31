    <div class="d-md-none d-lg-none" style="position: absolute; top: 76px; z-index: 2;">
      <a class="btn btn-primary d-flex rounded-start-0 rounded-pill fw-bold" href="popular.php">
        <i class="bi bi-star-fill rotating-class me-1"></i> Popular
      </a>
    </div>
    <div class="d-none d-md-block d-lg-block end-0" style="position: absolute; top: 53px; z-index: 2;">
      <a class="btn btn-primary d-flex rounded-end-0 rounded-pill fw-bold" data-bs-toggle="collapse" href="#collapsePopular" role="button" aria-expanded="false" aria-controls="collapsePopular" id="toggleButton">
        Show Popular
      </a>
    </div>

    <script>
      // Wait for the DOM to be ready
      document.addEventListener("DOMContentLoaded", function() {
        const toggleButton = document.getElementById("toggleButton");
        const collapsePopular = document.getElementById("collapsePopular");

        toggleButton.addEventListener("click", function() {
          if (collapsePopular.classList.contains("show")) {
            toggleButton.textContent = "Show Popular";
            collapsePopular.setAttribute("aria-expanded", "false");
          } else {
            toggleButton.textContent = "Hide Popular";
            collapsePopular.setAttribute("aria-expanded", "true");
          }
        });

        // Listen for the hidden.bs.collapse event to reset the button text when the element is hidden.
        collapsePopular.addEventListener("hidden.bs.collapse", function() {
          toggleButton.textContent = "Show Popular";
        });
      });
    </script>

