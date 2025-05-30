    <!-- Image Filter Modal -->
    <div class="modal fade" id="imageFilterModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
          <div class="modal-header border-0">
            <h5 class="modal-title">Filter</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="get" class="modal-body border-0">

            <?php if (isset($_GET['by'])): ?>
              <input type="hidden" name="by" value="<?php echo $_GET['by']; ?>">
            <?php endif; ?>
            <?php if (isset($_GET['page'])): ?>
              <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
            <?php endif; ?>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="text" name="q" id="q" placeholder="General Search..." maxlength="500" value="<?php echo htmlspecialchars(isset($_GET['q']) ? $_GET['q'] : '', ENT_QUOTES); ?>">  
              <label for="q" class="fw-medium">General Search...</label>
            </div>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="text" name="character" id="character" placeholder="Character" maxlength="500" value="<?php echo htmlspecialchars(isset($_GET['character']) ? $_GET['character'] : '', ENT_QUOTES); ?>">  
              <label for="character" class="fw-medium">Character</label>
            </div>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="text" name="parody" id="parody" placeholder="Parody" maxlength="500" value="<?php echo htmlspecialchars(isset($_GET['parody']) ? $_GET['parody'] : '', ENT_QUOTES); ?>">  
              <label for="parody" class="fw-medium">Parody</label>
            </div>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="text" name="group" id="group" placeholder="Group" maxlength="500" value="<?php echo htmlspecialchars(isset($_GET['group']) ? $_GET['group'] : '', ENT_QUOTES); ?>">  
              <label for="group" class="fw-medium">Group</label>
            </div>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="text" name="tag" id="tag" placeholder="Tags (comma-separated)" maxlength="500" value="<?php echo htmlspecialchars(isset($_GET['tag']) ? $_GET['tag'] : '', ENT_QUOTES); ?>">  
              <label for="tag" class="fw-medium">Tags (comma-separated)</label>
            </div>
            <div class="form-floating mb-2">
              <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="number" name="uid" id="uid" placeholder="User ID" maxlength="15" min="1" value="<?php echo htmlspecialchars(isset($_GET['uid']) ? $_GET['uid'] : '', ENT_QUOTES); ?>">  
              <label for="uid" class="fw-medium">User ID</label>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-md-4">
                <div class="form-floating">
                  <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="number" name="year" id="year" placeholder="Year (e.g. 2023)" min="1900" max="2100" value="<?php echo htmlspecialchars($_GET['year'] ?? '', ENT_QUOTES); ?>">
                  <label for="year" class="fw-medium">Year (e.g. 2023)</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating">
                  <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="number" name="month" id="month" placeholder="Month (1-12)" min="1" max="12" value="<?php echo htmlspecialchars($_GET['month'] ?? '', ENT_QUOTES); ?>">
                  <label for="month" class="fw-medium">Month (1-12)</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating">
                  <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="number" name="day" id="day" placeholder="Day (1-31)" min="1" max="31" value="<?php echo htmlspecialchars($_GET['day'] ?? '', ENT_QUOTES); ?>">
                  <label for="day" class="fw-medium">Day (1-31)</label>
                </div>
              </div>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <div class="form-floating">
                  <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="number" name="year_start" id="year_start" placeholder="Year Start" min="1900" max="2100" value="<?php echo htmlspecialchars($_GET['year_start'] ?? '', ENT_QUOTES); ?>">
                  <label for="year_start" class="fw-medium">Year Start</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating">
                  <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="number" name="year_end" id="year_end" placeholder="Year End" min="1900" max="2100" value="<?php echo htmlspecialchars($_GET['year_end'] ?? '', ENT_QUOTES); ?>">
                  <label for="year_end" class="fw-medium">Year End</label>
                </div>
              </div>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <div class="form-floating">
                  <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="number" name="month_start" id="month_start" placeholder="Month Start" min="1" max="12" value="<?php echo htmlspecialchars($_GET['month_start'] ?? '', ENT_QUOTES); ?>">
                  <label for="month_start" class="fw-medium">Month Start</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating">
                  <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="number" name="month_end" id="month_end" placeholder="Month End" min="1" max="12" value="<?php echo htmlspecialchars($_GET['month_end'] ?? '', ENT_QUOTES); ?>">
                  <label for="month_end" class="fw-medium">Month End</label>
                </div>
              </div>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <div class="form-floating">
                  <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="number" name="day_start" id="day_start" placeholder="Day Start" min="1" max="31" value="<?php echo htmlspecialchars($_GET['day_start'] ?? '', ENT_QUOTES); ?>">
                  <label for="day_start" class="fw-medium">Day Start</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating">
                  <input class="form-control rounded-3 fw-medium border-0 bg-body-tertiary shadow focus-ring focus-ring-light" type="number" name="day_end" id="day_end" placeholder="Day End" min="1" max="31" value="<?php echo htmlspecialchars($_GET['day_end'] ?? '', ENT_QUOTES); ?>">
                  <label for="day_end" class="fw-medium">Day End</label>
                </div>
              </div>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <select class="form-select border-0 bg-body-tertiary shadow focus-ring focus-ring-light rounded-3 fw-mediu" style="height: 58px;" name="artwork_type" aria-label="Large select example">
                  <option value="" <?php echo (!isset($_GET['artwork_type']) || $_GET['artwork_type'] === '') ? 'selected' : ''; ?>>Any Artwork Type</option>
                  <option value="illustration" <?php echo (isset($_GET['artwork_type']) && $_GET['artwork_type'] === 'illustration') ? 'selected' : ''; ?>>Illustration</option>
                  <option value="manga" <?php echo (isset($_GET['artwork_type']) && $_GET['artwork_type'] === 'manga') ? 'selected' : ''; ?>>Manga</option>
                </select>
              </div>
              <div class="col-md-6">
                <select class="form-select border-0 bg-body-tertiary shadow focus-ring focus-ring-light rounded-3 fw-medium" style="height: 58px;" name="type" aria-label="Large select example">
                  <option value="" <?php echo (!isset($_GET['type']) || $_GET['type'] === '') ? 'selected' : ''; ?>>Any Type</option>
                  <option value="safe" <?php echo (isset($_GET['type']) && $_GET['type'] === 'safe') ? 'selected' : ''; ?>>Safe For Works</option>
                  <option value="nsfw" <?php echo (isset($_GET['type']) && $_GET['type'] === 'nsfw') ? 'selected' : ''; ?>>NSFW/R-18</option>
                </select>
              </div>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <select class="form-select border-0 bg-body-tertiary shadow focus-ring focus-ring-light rounded-3 fw-mediu" style="height: 58px;" name="category" aria-label="Large select example">
                  <option value="" <?php echo (!isset($_GET['category']) || $_GET['category'] === '') ? 'selected' : ''; ?>>Any Category</option>
                  <option value="artworks/illustrations" <?php echo (isset($_GET['category']) && $_GET['category'] === 'artworks/illustrations') ? 'selected' : ''; ?>>artworks/illustrations</option>
                  <option value="3DCG" <?php echo (isset($_GET['category']) && $_GET['category'] === '3DCG') ? 'selected' : ''; ?>>3DCG</option>
                  <option value="real" <?php echo (isset($_GET['category']) && $_GET['category'] === 'real') ? 'selected' : ''; ?>>real</option>
                  <option value="MMD" <?php echo (isset($_GET['category']) && $_GET['category'] === 'MMD') ? 'selected' : ''; ?>>MMD</option>
                  <option value="multi-work series" <?php echo (isset($_GET['category']) && $_GET['category'] === 'multi-work series') ? 'selected' : ''; ?>>multi-work series</option>
                  <option value="manga series" <?php echo (isset($_GET['category']) && $_GET['category'] === 'manga series') ? 'selected' : ''; ?>>manga series</option>
                  <option value="doujinshi series" <?php echo (isset($_GET['category']) && $_GET['category'] === 'doujinshi series') ? 'selected' : ''; ?>>doujinshi series</option>
                  <option value="oneshot manga" <?php echo (isset($_GET['category']) && $_GET['category'] === 'oneshot manga') ? 'selected' : ''; ?>>oneshot manga</option>
                  <option value="oneshot doujinshi" <?php echo (isset($_GET['category']) && $_GET['category'] === 'oneshot doujinshi') ? 'selected' : ''; ?>>oneshot doujinshi</option>
                  <option value="doujinshi" <?php echo (isset($_GET['category']) && $_GET['category'] === 'doujinshi') ? 'selected' : ''; ?>>doujinshi</option>
                </select>
              </div>
              <div class="col-md-6">
                <select class="form-select border-0 bg-body-tertiary shadow focus-ring focus-ring-light rounded-3 fw-mediu" style="height: 58px;" name="language" aria-label="Large select example">
                  <option value="" <?php echo (!isset($_GET['language']) || $_GET['language'] === '') ? 'selected' : ''; ?>>Choose Language</option>
                  <option value="English" <?php echo (isset($_GET['language']) && $_GET['language'] === 'English') ? 'selected' : ''; ?>>English</option>
                  <option value="Japanese" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Japanese') ? 'selected' : ''; ?>>Japanese</option>
                  <option value="Chinese" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Chinese') ? 'selected' : ''; ?>>Chinese</option>
                  <option value="Korean" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Korean') ? 'selected' : ''; ?>>Korean</option>
                  <option value="Russian" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Russian') ? 'selected' : ''; ?>>Russian</option>
                  <option value="Indonesian" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Indonesian') ? 'selected' : ''; ?>>Indonesian</option>
                  <option value="Spanish" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Spanish') ? 'selected' : ''; ?>>Spanish</option>
                  <option value="Other" <?php echo (isset($_GET['language']) && $_GET['language'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                  <option value="None" <?php echo (isset($_GET['language']) && $_GET['language'] === 'None') ? 'selected' : ''; ?>>None</option>
                </select>
              </div>
            </div>
    
            <div class="btn-group gap-2 w-100">
              <button type="submit" class="btn btn-primary w-50 rounded fw-medium">Apply Filters</button>
              <a href="./" class="btn btn-secondary w-50 rounded fw-medium">Clear All Filters</a>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- End of Image Filter Modal -->