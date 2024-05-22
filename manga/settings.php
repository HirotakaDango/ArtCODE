    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Settings</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST">
            <div class="modal-body border-0">
              <div class="form-floating mb-3">
                <input type="text" class="form-control" id="floatingInput" name="website_url" value="<?php echo $websiteUrl; ?>" placeholder="Website address">
                <label for="floatingInput">Website address</label>
              </div>
              <div class="form-floating mb-3">
                <input type="text" class="form-control" id="floatingInput" name="folder_path" value="<?php echo $folderPath; ?>" placeholder="Images path">
                <label for="floatingInput">Images path</label>
              </div>
              <div class="form-floating mb-3">
                <input type="text" class="form-control" id="floatingInput" name="thumb_path" value="<?php echo $thumbPath; ?>" placeholder="Thumbnails path">
                <label for="floatingInput">Thumbnails path</label>
              </div>
              <div class="form-floating mb-3">
                <input type="text" class="form-control" id="floatingInput" name="number_page" value="<?php echo $numPage; ?>" placeholder="Number per page">
                <label for="floatingInput">Number per page</label>
              </div>
              <div class="btn-group w-100 gap-2 border-0">
                <button type="button" class="w-50 rounded fw-bold btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="w-50 rounded fw-bold btn btn-primary">Save changes</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>