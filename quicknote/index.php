<?php
require_once('../auth.php');

$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ArtCODE QuickNote</title>
    <link rel="icon" type="image/svg+xml" href="/icon/favicon.png" />
    <?php include('../bootstrapcss.php'); ?>

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="/" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="ArtCODE QuickNote" />
    <meta property="og:description" content="This is a simple note taking app." />
    <meta property="og:image" content="/icon/favicon.png" />

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="ArtCODE QuickNote" />
    <meta property="twitter:domain" content="/" />
    <meta property="twitter:url" content="" />
    <meta name="twitter:title" content="NoteTakeApp" />
    <meta name="twitter:description" content="This is a simple note taking app." />
    <meta name="twitter:image" content="/icon/favicon.png" />

    <style>
      .note-card {
        cursor: pointer;
        transition: transform 0.2s;
      }
      .note-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
      .note-card:hover {
        transform: scale(1.02);
      }
      .action-btn {
        background: none;
        border: none;
        color: inherit;
      }
      .category-badge {
        font-size: 0.7em;
        margin-left: 0.5em;
        background: #6c757d;
        color: #fff;
        border-radius: 0.5em;
        padding: 0.15em 0.6em;
      }
      @media (max-width: 991.98px) {
        #singleNoteSidebarColumn {
          display: none !important;
        }
      }
      @media (min-width: 992px) {
        #singleNoteSidebarColumn {
          display: flex !important;
        }
      }
      #singleNoteDualCol {
        min-height: 100vh;
        max-height: 100vh;
        width: 100vw;
        overflow: hidden;
        margin: 0;
        padding: 0;
      }
      #singleNoteSidebarColumn {
        border-right: 1px solid #343a40;
        min-width: 350px;
        max-width: 400px;
        background: #23272b;
        height: 100vh;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
      }
      #singleNoteSidebarColumn .note-card {
        margin-bottom: 0.5rem;
        border-left: 4px solid transparent;
        border-radius: 0 0.5rem 0.5rem 0;
      }
      #singleNoteSidebarColumn .note-card.active {
        border-left: 4px solid #0d6efd;
        background-color: #222b34;
      }
      #singleNoteMainColumn {
        flex: 1;
        min-width: 0;
        background: var(--bs-body-bg);
        display: flex;
        flex-direction: column;
        height: 100vh;
        overflow: hidden;
      }
      #singleNoteSidebarColumn::-webkit-scrollbar {
        width: 0px;
        background: transparent;
      }
      #singleNoteSidebarColumn {
        -ms-overflow-style: none;
        scrollbar-width: none;
      }
      #wordCounter {
        position: fixed;
        bottom: 0;
        right: 0;
        font-size: 13px;
        font-weight: 500;
        z-index: 1055;
        background: #282c34d9;
        color: white;
        border-radius: 8px 0 0 0;
        padding: 0.3em 1.3em;
        user-select: none;
        pointer-events: none;
      }
      #noteContent {
        width: 100%;
        border: none;
        outline: none;
        background: #222b34;
        color: #fff;
        resize: none;
        padding: 2rem 2.5rem 2rem 2.5rem;
        font-size: 1rem;
      }
      .word-counter-container { display: none !important; }
      .single-note-card-actions {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        z-index: 2;
      }
      .single-note-card-content {
        padding-right: 2.5rem;
      }
      .dropdown-item-category {
        display: flex;
        align-items: center;
        gap: 0.5em;
      }
      .add-category-btn {
        margin-top: 0.5em;
      }
      .categorize-selected-btn {
        margin-left: 8px;
      }
      .categories-sort-row {
        display: flex;
        gap: 12px;
        justify-content: flex-start;
        align-items: center;
        margin-bottom: 1rem;
      }
      .categories-sort-row .form-select { min-width: 180px; }
      .categories-sort-row .form-control { min-width: 180px; }
      .categories-sort-row .sort-select { margin-left: auto; min-width: 120px; }
      .categories-sort-row .search-input { margin-left: 0; flex: 1 1 auto; }
      .copied-indicator {
        position: absolute;
        top: 0.5rem;
        right: 3.3rem;
        z-index: 10;
        background: #2bc48a;
        color: #fff;
        font-weight: bold;
        border-radius: 6px;
        padding: 0.15em 0.6em;
        font-size: 0.95em;
        opacity: 0;
        transition: opacity 0.2s;
        pointer-events: none;
      }
      .copied-indicator.show {
        opacity: 1;
      }
      #installButton {
        position: fixed;
        bottom: 22px;
        left: 22px;
        z-index: 2000;
        display: none;
      }
      /* Pagination controls */
      .pagination-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin: 16px 0 0 0;
        user-select: none;
      }
      .pagination-btn {
        border: none;
        background: #23272b;
        color: #fff;
        border-radius: 6px;
        padding: 0.2em 1em;
        font-size: 1em;
        cursor: pointer;
        transition: background .15s;
      }
      .pagination-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
        background: #333;
      }
      .pagination-page {
        font-weight: bold;
        font-size: 1em;
        color: #0d6efd;
      }
      .load-more-btn {
        display: block;
        width: 90%;
        margin: 0.7em auto 1em auto;
        border: none;
        border-radius: 10px;
        background: #343a40;
        color: #fff;
        font-weight: 500;
        font-size: 1em;
        padding: 0.4em 0;
        transition: background .15s;
      }
      .load-more-btn:hover:not(:disabled) {
        background: #0d6efd;
      }
    </style>
  </head>
  <body>
    <?php include('../header.php'); ?>
    <div class="w-100">
      <!-- All Notes View -->
      <div class="container mb-5 mt-3" id="allNotesView">
        <ul class="nav nav-tabs border-0 my-2 bg-dark-subtle p-3 rounded-4 align-items-center">
          <li class="nav-item">
            <input id="searchInput" class="form-control rounded search-input w-100 border-0" type="text" placeholder="🔍 Search..." />
          </li>
          <li class="nav-item ms-auto">
            <a class="nav-link active border-0 rounded" href="#" id="allNotesTab"><i class="bi bi-card-text"></i></a>
          </li>
          <li class="nav-item">
            <a class="nav-link border-0 rounded" href="#" id="favoritesTab"><i class="bi bi-star-fill"></i></a>
          </li>
          <div class="dropdown">
            <button class="btn border-0 rounded" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              &#9776;
            </button>
            <ul class="dropdown-menu rounded-4">
              <li>
                <button id="newNoteBtn" class="dropdown-item">
                  <i class="bi bi-plus-circle"></i> New Note
                </button>
              </li>
              <li>
                <button id="addCategoryBtn" class="dropdown-item dropdown-item-category">
                  <i class="bi bi-tag"></i> Add Category
                </button>
              </li>
              <div class="px-3">
                <div class="border border-2 my-2"></div>
              </div>
              <li>
                <button id="exportBtn" class="dropdown-item">
                  <i class="bi bi-download"></i> Export
                </button>
              </li>
              <li>
                <button id="importBtn" class="dropdown-item">
                  <i class="bi bi-upload"></i> Import
                </button>
              </li>
            </ul>
          </div>
        </ul>
        <!-- Selected notes actions -->
        <div id="selectedNotesActions" class="my-4" style="display: none;">
          <div class="d-flex justify-content-between align-items-center">
            <div class="select-all-container align-items-center">
              <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
              <label for="selectAllCheckbox" class="mx-2">Select All</label>
            </div>
            <div>
              <button id="categorizeSelectedBtn" class="btn btn-secondary btn-sm ms-2 categorize-selected-btn" title="Set Category">
                <i class="bi bi-tags"></i> Categorize
              </button>
              <button id="deleteSelectedBtn" class="btn btn-danger btn-sm ms-2">
                <i class="bi bi-trash3"></i> Delete (<span id="selectedCount">0</span>)
              </button>
            </div>
          </div>
        </div>
        <div class="categories-sort-row">
          <select id="categoryFilter" class="form-select"></select>
          <select id="sortSelect" class="form-select sort-select" style="width: auto;">
            <option value="newest">Newest</option>
            <option value="oldest">Oldest</option>
          </select>
        </div>
        <div id="noteList" class="list-group">
          <!-- Notes will be listed here -->
        </div>
        <div id="paginationContainer" class="pagination-container"></div>
      </div>
      <!-- Single Note Dual Column View -->
      <div id="singleNoteView" style="display: none;">
        <div id="singleNoteDualCol" class="d-flex flex-row">
          <!-- Sidebar: Note List (desktop only) -->
          <div id="singleNoteSidebarColumn" class="d-flex flex-column vh-100 overflow-auto p-3 gap-1" style="display: flex;min-width:350px;max-width:420px;">
            <div class="fw-bold mb-2">All Notes</div>
            <div id="singleNoteSidebarList" class="flex-grow-1"></div>
            <button id="sidebarLoadMoreBtn" class="load-more-btn w-100" style="display:none;">Load More...</button>
          </div>
          <!-- Main: Note Content -->
          <div id="singleNoteMainColumn" class="flex-grow-1 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center bg-dark-subtle py-1 px-3">
              <div class="d-flex gap-3">
                <button id="backBtn" class="btn border-0">
                  <i class="bi bi-chevron-left link-body-emphasis" style="-webkit-text-stroke: 2px;"></i>
                </button>
                <button id="undoBtn" class="btn border-0">
                  <i class="bi bi-arrow-counterclockwise"></i>
                </button>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span id="noteDate" class="text-muted"></span>
                <span>|</span>
                <select id="noteCategorySelect" class="form-select form-select-sm w-auto ms-0"></select>
              </div>
              <div class="d-flex gap-3">
                <button id="redoBtn" class="btn border-0">
                  <i class="bi bi-arrow-clockwise"></i>
                </button>
                <button id="deleteSingleBtn" class="btn border-0">
                  <i class="bi bi-trash3-fill link-body-emphasis"></i>
                </button>
              </div>
            </div>
            <textarea id="noteContent"
              style="height: calc(100svh - 44px); max-height: calc(100svh - 44px); min-height: calc(100svh - 44px); box-sizing: border-box;"
              class="form-control rounded-0 p-4 border-0 bg-dark-subtle focus-ring focus-ring-dark flex-grow-1"></textarea>
          </div>
        </div>
      </div>
    </div>
    <input type="file" id="importInput" style="display: none;" accept=".json" />
    <div id="wordCounter" style="display: none;">Words: 0</div>

    <button id="installButton" class="btn btn-primary shadow-lg">
      <i class="bi bi-download"></i> Install App
    </button>

    <script>
      // --- Data & Storage ---
      let notes = {};
      let currentNote = null;
      let currentCategory = 'all';
      let selectedNotes = new Set();
      let filteredNoteIds = [];
      let undoStack = [];
      let redoStack = [];
      let lastRecordedContent = '';
      let autoSaveTimeout = null;
      // Categories (dynamically managed)
      let categories = [];
      // --- Pagination State ---
      const PAGE_SIZE = 25;
      let currentPage = 1;
      let sidebarPage = 1;
  
      // --- DOM ---
      const allNotesView = document.getElementById('allNotesView');
      const singleNoteView = document.getElementById('singleNoteView');
      const noteList = document.getElementById('noteList');
      const searchInput = document.getElementById('searchInput');
      const sortSelect = document.getElementById('sortSelect');
      const newNoteBtn = document.getElementById('newNoteBtn');
      const exportBtn = document.getElementById('exportBtn');
      const importBtn = document.getElementById('importBtn');
      const importInput = document.getElementById('importInput');
      const allNotesTab = document.getElementById('allNotesTab');
      const favoritesTab = document.getElementById('favoritesTab');
      const deleteSingleBtn = document.getElementById('deleteSingleBtn');
      const backBtn = document.getElementById('backBtn');
      const undoBtn = document.getElementById('undoBtn');
      const redoBtn = document.getElementById('redoBtn');
      const noteContent = document.getElementById('noteContent');
      const noteDate = document.getElementById('noteDate');
      const wordCounter = document.getElementById('wordCounter');
      const selectedNotesActions = document.getElementById('selectedNotesActions');
      const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
      const selectAllCheckbox = document.getElementById('selectAllCheckbox');
      const selectedCount = document.getElementById('selectedCount');
      const categoryFilter = document.getElementById('categoryFilter');
      const singleNoteSidebarList = document.getElementById('singleNoteSidebarList');
      const addCategoryBtn = document.getElementById('addCategoryBtn');
      const noteCategorySelect = document.getElementById('noteCategorySelect');
      const categorizeSelectedBtn = document.getElementById('categorizeSelectedBtn');
      const installButton = document.getElementById('installButton');
      const paginationContainer = document.getElementById('paginationContainer');
      const sidebarLoadMoreBtn = document.getElementById('sidebarLoadMoreBtn');
  
      // --- Category dropdowns ---
      function renderCategoryFilterDropdown() {
        categoryFilter.innerHTML = `<option value="all">All Categories</option>`;
        for (const cat of categories) {
          categoryFilter.innerHTML += `<option value="${cat}">${cat}</option>`;
        }
        categoryFilter.value = categoryFilter.value || "all";
      }
      function renderNoteCategoryDropdown(selectedCat) {
        noteCategorySelect.innerHTML = '';
        if (!categories.length) {
          categories.push('all');
        }
        for (const cat of categories) {
          let opt = document.createElement('option');
          opt.value = cat;
          opt.textContent = cat;
          if (selectedCat === cat) opt.selected = true;
          noteCategorySelect.appendChild(opt);
        }
      }
      // --- Note CRUD ---
      function loadNotes() {
        try {
          notes = JSON.parse(localStorage.getItem('notes')) || {};
        } catch { notes = {}; }
        // load categories from notes if any
        categories = [];
        for (let n of Object.values(notes)) {
          if (n.category && !categories.includes(n.category)) categories.push(n.category);
        }
        if (!categories.length) categories.push('all');
      }
      function saveNotes() {
        localStorage.setItem('notes', JSON.stringify(notes));
      }
      function createNote() {
        if (!categories.length) categories.push("all");
        const noteId = Date.now().toString();
        notes[noteId] = {
          title: 'New Note',
          content: '',
          date: new Date().toISOString(),
          favorite: false,
          category: categories[0] || "all"
        };
        currentNote = noteId;
        saveNotes();
        showSingleNote();
        updateTotalWordCounter();
      }
      function deleteNote() {
        if (currentNote && notes[currentNote]) {
          if (confirm('Are you sure you want to delete this note?')) {
            delete notes[currentNote];
            saveNotes();
            // Show the newest note after delete, or go back to main menu if none
            let noteArr = Object.entries(notes).sort((a, b) => new Date(b[1].date) - new Date(a[1].date));
            if (noteArr.length > 0) {
              currentNote = noteArr[0][0];
              showSingleNote();
            } else {
              currentNote = null;
              showAllNotes();
            }
          }
        }
      }
      function deleteNoteFromList(noteId) {
        if (notes[noteId] && confirm('Are you sure you want to delete this note?')) {
          delete notes[noteId];
          selectedNotes.delete(noteId);
          saveNotes();
          updateNoteList();
          updateSelectedNotesUI();
          if (noteId === currentNote) {
            let noteArr = Object.entries(notes).sort((a, b) => new Date(b[1].date) - new Date(a[1].date));
            if (noteArr.length > 0) {
              currentNote = noteArr[0][0];
              showSingleNote();
            } else {
              currentNote = null;
              showAllNotes();
            }
          }
        }
      }
      function toggleFavorite(noteId) {
        if (notes[noteId]) {
          notes[noteId].favorite = !notes[noteId].favorite;
          saveNotes();
          updateNoteList();
        }
      }
      function saveNote() {
        if (currentNote && notes[currentNote]) {
          notes[currentNote].content = noteContent.value || '';
          const titleLine = notes[currentNote].content.split('\n')[0] || 'Untitled';
          notes[currentNote].title = titleLine;
          notes[currentNote].date = new Date().toISOString();
          notes[currentNote].category = noteCategorySelect.value || "all";
          saveNotes();
          updateNoteList();
          updateTotalWordCounter();
        }
      }
      function exportNotes() {
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(notes));
        const a = document.createElement('a');
        a.href = dataStr;
        a.download = "notes_export.json";
        document.body.appendChild(a);
        a.click();
        a.remove();
      }
      function importNotes(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (evt) {
          try {
            const imported = JSON.parse(evt.target.result);
            notes = {...notes, ...imported};
            saveNotes();
            loadNotes();
            renderCategoryFilterDropdown();
            updateNoteList();
            alert('Notes imported successfully!');
          } catch {
            alert("Failed to import. File is not valid JSON.");
          }
        };
        reader.readAsText(file);
      }
      // --- UI: List and Sidebar Cards ---
      function getCategoryBadge(cat) {
        return `<span class="category-badge">${cat}</span>`;
      }
      function showCopiedIndicator(parentEl) {
        let indicator = parentEl.querySelector('.copied-indicator');
        if (!indicator) {
          indicator = document.createElement('span');
          indicator.className = 'copied-indicator';
          indicator.textContent = 'Copied!';
          parentEl.appendChild(indicator);
        }
        indicator.classList.add('show');
        setTimeout(() => indicator.classList.remove('show'), 1200);
      }
      function renderNoteCard(note, noteId, {showCheckbox=false, selected=false, showCategory=true, showActions=true, active=false, limitChars=200}={}) {
        let card = document.createElement('div');
        let isSingleViewSidebar = !showCheckbox && showActions && limitChars===200 && !selected;
        card.className = 'card bg-body-tertiary mb-3 note-card border-0 rounded-3 position-relative' + (isSingleViewSidebar ? '' : ' ps-5');
        if (active) card.classList.add('active');
        card.style.cursor = 'pointer';
        card.style.position = 'relative';
        if (showCheckbox) {
          const cbDiv = document.createElement('div');
          cbDiv.className = "position-absolute top-50 start-0 p-0 translate-middle-y ms-4";
          const cb = document.createElement('input');
          cb.type = 'checkbox';
          cb.className = 'form-check-input note-checkbox';
          cb.checked = selected;
          cb.addEventListener('change', e => {
            e.stopPropagation();
            if (cb.checked) selectedNotes.add(noteId);
            else selectedNotes.delete(noteId);
            updateSelectedNotesUI();
          });
          cbDiv.appendChild(cb);
          card.appendChild(cbDiv);
        }
        let cardBody = document.createElement('div');
        cardBody.className = "card-body";
        let noteHeader = document.createElement('div');
        noteHeader.className = "note-header";
        let dateElem = document.createElement('small');
        dateElem.className = "fw-medium";
        dateElem.textContent = new Date(note.date).toLocaleDateString('en-US');
        let categoryElem = document.createElement('span');
        categoryElem.className = "category-badge ms-1";
        categoryElem.textContent = note.category || "all";
        let leftHeader = document.createElement('span');
        leftHeader.appendChild(dateElem);
        if (showCategory) {
          leftHeader.appendChild(categoryElem);
        }
        let buttonsDiv = document.createElement('div');
        buttonsDiv.className = "";
        if (showActions) {
          let copyBtn = document.createElement('button');
          copyBtn.className = 'action-btn copy-btn';
          copyBtn.innerHTML = '<i class="bi bi-copy"></i>';
          copyBtn.onclick = e => {
            e.preventDefault(); e.stopPropagation();
            navigator.clipboard.writeText(note.content || '').then(() => {
              showCopiedIndicator(card);
            });
          };
          let downloadBtn = document.createElement('button');
          downloadBtn.className = 'action-btn download-btn';
          downloadBtn.innerHTML = '<i class="bi bi-download"></i>';
          downloadBtn.onclick = e => {
            e.stopPropagation();
            const content = note.content || '';
            let fn = (content.split('.')[0] || '').trim().split(/\s+/).slice(0,4).join(' ') || "Untitled";
            let filename = prompt("Enter filename:", fn);
            if (filename === null) return;
            filename = filename.trim() === "" ? fn : filename.trim();
            const blob = new Blob([content], {type: 'text/plain;charset=utf-8'});
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob); a.download = filename + ".txt"; a.click();
            URL.revokeObjectURL(a.href);
          };
          let favBtn = document.createElement('button');
          favBtn.className = 'action-btn favorite-btn';
          favBtn.innerHTML = `<i class="bi ${note.favorite ? "bi-star-fill":"bi-star"}"></i>`;
          favBtn.onclick = e => { e.stopPropagation(); toggleFavorite(noteId); };
          let delBtn = document.createElement('button');
          delBtn.className = 'action-btn delete-btn';
          delBtn.innerHTML = '<i class="bi bi-trash3-fill"></i>';
          delBtn.onclick = e => { e.stopPropagation(); deleteNoteFromList(noteId); };
          buttonsDiv.append(copyBtn, downloadBtn, favBtn, delBtn);
        }
        noteHeader.append(leftHeader, buttonsDiv);
        let contentDiv = document.createElement('div');
        contentDiv.className = 'mt-3';
        let safeContent = (note.content||'').substring(0, limitChars)+(note.content.length>limitChars?"...":"");
        contentDiv.textContent = safeContent;
        cardBody.append(noteHeader, contentDiv);
        card.append(cardBody);
        if (showActions) {
          buttonsDiv.className = "single-note-card-actions";
          cardBody.classList.add("single-note-card-content");
        }
        return card;
      }
  
      // --- Pagination for allNotesView ---
      function getFilteredNotesArr() {
        let query = (searchInput.value || '').toLowerCase();
        let catFilter = categoryFilter.value;
        let arr = Object.entries(notes).filter(([id,n]) =>
          ((n.title && n.title.toLowerCase().includes(query)) ||
          (n.content && n.content.toLowerCase().includes(query)))
          && (currentCategory !== "favorites" || n.favorite)
          && (catFilter==="all" || n.category===catFilter)
        );
        arr = arr.sort((a, b) => {
          const da = new Date(a[1].date), db = new Date(b[1].date);
          return sortSelect.value === 'newest' ? db-da : da-db;
        });
        return arr;
      }
      function updateNoteList() {
        if (!noteList) return;
        noteList.innerHTML = '';
        let notesArr = getFilteredNotesArr();
        filteredNoteIds = notesArr.map(([id])=>id);
        // Pagination logic
        let totalPages = Math.ceil(notesArr.length / PAGE_SIZE) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        let pageNotes = notesArr.slice((currentPage-1)*PAGE_SIZE, currentPage*PAGE_SIZE);
        pageNotes.forEach(([noteId, note]) => {
          let card = renderNoteCard(note, noteId, {
            showCheckbox:true, selected:selectedNotes.has(noteId), showCategory:true, showActions:true, limitChars:200
          });
          card.onclick = e => { if (!e.target.closest('button') && !e.target.closest('input')) { currentNote = noteId; showSingleNote(); } };
          noteList.appendChild(card);
        });
        renderPagination(notesArr.length, totalPages);
  
        updateSelectedNotesUI();
      }
      function renderPagination(totalNotes, totalPages) {
        paginationContainer.innerHTML = '';
        if (totalPages <= 1) return;
        // Prev Button
        let prevBtn = document.createElement('button');
        prevBtn.className = 'pagination-btn';
        prevBtn.innerHTML = '&lt; Prev';
        prevBtn.disabled = currentPage <= 1;
        prevBtn.onclick = () => { currentPage = Math.max(1, currentPage-1); updateNoteList(); };
        paginationContainer.appendChild(prevBtn);
        // Page number
        let pageText = document.createElement('span');
        pageText.className = 'pagination-page';
        pageText.textContent = `Page ${currentPage} of ${totalPages}`;
        paginationContainer.appendChild(pageText);
        // Next Button
        let nextBtn = document.createElement('button');
        nextBtn.className = 'pagination-btn';
        nextBtn.innerHTML = 'Next &gt;';
        nextBtn.disabled = currentPage >= totalPages;
        nextBtn.onclick = () => { currentPage = Math.min(totalPages, currentPage+1); updateNoteList(); };
        paginationContainer.appendChild(nextBtn);
      }
  
      // --- Sidebar pagination for singleNoteView ---
      function getFilteredSidebarArr() {
        let filter = (searchInput.value||'').toLowerCase();
        let catFilter = categoryFilter.value;
        let arr = Object.entries(notes).filter(([id,n]) =>
          ((n.title && n.title.toLowerCase().includes(filter)) ||
          (n.content && n.content.toLowerCase().includes(filter)))
          && (catFilter==="all" || n.category===catFilter)
        );
        arr = arr.sort((a, b) => {
          const da = new Date(a[1].date), db = new Date(b[1].date);
          return sortSelect.value === 'newest' ? db-da : da-db;
        });
        return arr;
      }
      function renderSingleNoteSidebarList(resetPage) {
        if (resetPage) sidebarPage = 1;
        singleNoteSidebarList.innerHTML = '';
        let arr = getFilteredSidebarArr();
        let shownArr = arr.slice(0, sidebarPage * PAGE_SIZE);
        shownArr.forEach(([noteId, note]) => {
          let card = renderNoteCard(note, noteId, {
            showCheckbox:false, showCategory:true, showActions:true, active:noteId===currentNote, limitChars:200
          });
          card.onclick = e => { if (!e.target.closest('button') && !e.target.closest('input')) { currentNote = noteId; showSingleNote(); } };
          singleNoteSidebarList.appendChild(card);
        });
        // Load more logic
        if (arr.length > shownArr.length) {
          sidebarLoadMoreBtn.style.display = '';
        } else {
          sidebarLoadMoreBtn.style.display = 'none';
        }
      }
  
      // --- Multi-select UI ---
      function updateSelectedNotesUI() {
        if (!selectedNotesActions || !selectedCount) return;
        if (Object.keys(notes).length > 0) selectedNotesActions.style.display = 'block';
        else selectedNotesActions.style.display = 'none';
        selectedCount.textContent = selectedNotes.size;
        if (selectAllCheckbox) {
          if (filteredNoteIds.length > 0 && selectedNotes.size === filteredNoteIds.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
          } else if (selectedNotes.size > 0) {
            selectAllCheckbox.indeterminate = true;
          } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
          }
        }
      }
      function deleteSelectedNotes() {
        if (selectedNotes.size === 0) return;
        if (confirm(`Are you sure you want to delete ${selectedNotes.size} note(s)?`)) {
          selectedNotes.forEach(noteId => delete notes[noteId]);
          saveNotes();
          selectedNotes.clear();
          updateSelectedNotesUI();
          updateNoteList();
        }
      }
      // --- Categorize Multiple Selected ---
      function categorizeSelectedNotes() {
        if (selectedNotes.size === 0) return;
        let cat = prompt('Enter category for selected notes (no commas):', "");
        if (!cat) return;
        cat = cat.trim();
        if (!cat || cat.includes(',')) {
          alert("Invalid category.");
          return;
        }
        if (!categories.includes(cat)) {
          categories.push(cat);
          renderCategoryFilterDropdown();
        }
        selectedNotes.forEach(noteId => {
          if (notes[noteId]) notes[noteId].category = cat;
        });
        saveNotes();
        updateNoteList();
        renderSingleNoteSidebarList(true);
      }
      // --- Word Counter (total only) ---
      function updateTotalWordCounter() {
        if (!wordCounter || !noteContent) return;
        wordCounter.style.display = singleNoteView.style.display === 'block' ? 'block' : 'none';
        let text = noteContent.value || '';
        let words = text.trim().split(/\s+/).filter(w => w.length>0);
        wordCounter.textContent = 'Words: ' + words.length;
      }
      // --- Navigation ---
      function showAllNotes() {
        allNotesView.style.display = 'block';
        singleNoteView.style.display = 'none';
        currentNote = null;
        updateNoteList();
        wordCounter.style.display = 'none';
      }
      function showSingleNote() {
        allNotesView.style.display = 'none';
        singleNoteView.style.display = 'block';
        if (currentNote && notes[currentNote]) {
          noteDate.textContent = new Date(notes[currentNote].date).toLocaleDateString('en-US');
          noteContent.value = notes[currentNote].content || '';
          renderNoteCategoryDropdown(notes[currentNote].category || "all");
          noteCategorySelect.onchange = () => {
            notes[currentNote].category = noteCategorySelect.value;
            saveNotes();
            renderSingleNoteSidebarList(true);
            updateNoteList();
          };
          undoStack = [];
          redoStack = [];
          lastRecordedContent = noteContent.value;
          updateTotalWordCounter();
          renderSingleNoteSidebarList(true);
        }
      }
      // --- Category Management ---
      function addCategory() {
        let name = prompt("Enter new category name (no commas):", "");
        if (!name) return;
        name = name.trim();
        if (!name || name.includes(',') || categories.includes(name)) {
          alert("Invalid or duplicate category.");
          return;
        }
        categories.push(name);
        renderCategoryFilterDropdown();
        renderNoteCategoryDropdown(noteCategorySelect.value);
      }
      // --- Event Listeners ---
      function setupEventListeners() {
        categoryFilter.onchange = () => { currentPage = 1; updateNoteList(); };
        allNotesTab.onclick = e => { e.preventDefault(); currentCategory='all'; allNotesTab.classList.add('active'); favoritesTab.classList.remove('active'); currentPage = 1; updateNoteList(); };
        favoritesTab.onclick = e => { e.preventDefault(); currentCategory='favorites'; favoritesTab.classList.add('active'); allNotesTab.classList.remove('active'); currentPage = 1; updateNoteList(); };
        sortSelect.onchange = () => { currentPage = 1; updateNoteList(); };
        searchInput.oninput = () => { currentPage = 1; updateNoteList(); };
        newNoteBtn.onclick = createNote;
        exportBtn.onclick = exportNotes;
        importBtn.onclick = ()=>importInput.click();
        importInput.onchange = importNotes;
        deleteSingleBtn.onclick = deleteNote;
        backBtn.onclick = showAllNotes;
        if (selectAllCheckbox) {
          selectAllCheckbox.onchange = () => {
            if (selectAllCheckbox.checked) filteredNoteIds.forEach(id => selectedNotes.add(id));
            else selectedNotes.clear();
            updateNoteList();
            updateSelectedNotesUI();
          };
        }
        if (deleteSelectedBtn) deleteSelectedBtn.onclick = deleteSelectedNotes;
        if (categorizeSelectedBtn) categorizeSelectedBtn.onclick = categorizeSelectedNotes;
        noteContent.oninput = () => {
          if (noteContent.value !== lastRecordedContent) {
            undoStack.push(lastRecordedContent);
            lastRecordedContent = noteContent.value;
            redoStack = [];
          }
          clearTimeout(autoSaveTimeout);
          autoSaveTimeout = setTimeout(() => { saveNote(); }, 250);
          updateTotalWordCounter();
        };
        undoBtn.onclick = () => {
          if (undoStack.length > 0) {
            const prev = undoStack.pop();
            redoStack.push(noteContent.value);
            noteContent.value = prev;
            lastRecordedContent = prev;
            saveNote();
            updateTotalWordCounter();
          }
        };
        redoBtn.onclick = () => {
          if (redoStack.length > 0) {
            const next = redoStack.pop();
            undoStack.push(noteContent.value);
            noteContent.value = next;
            lastRecordedContent = next;
            saveNote();
            updateTotalWordCounter();
          }
        };
        addCategoryBtn.onclick = addCategory;
        window.addEventListener('resize', updateTotalWordCounter);
        sidebarLoadMoreBtn.onclick = () => {
          sidebarPage += 1;
          renderSingleNoteSidebarList();
        };
      }
  
      // --- PWA Install Option (no manifest/sw.js needed) ---
      let deferredPrompt = null;
      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        installButton.style.display = 'block';
      });
      installButton.addEventListener('click', async () => {
        if (deferredPrompt) {
          deferredPrompt.prompt();
          await deferredPrompt.userChoice;
          deferredPrompt = null;
          installButton.style.display = 'none';
        }
      });
      window.addEventListener('appinstalled', () => {
        installButton.style.display = 'none';
        deferredPrompt = null;
      });
  
      // --- Service Worker/Offline Caching ---
      function registerServiceWorkerInline() {
        if ('serviceWorker' in navigator) {
          const swCode = `
            const CACHE_NAME = 'notetakeapp-v2';
            const urlsToCache = [
              location.href,
              'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
              'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
              'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'
            ];
            self.addEventListener('install', e => {
              e.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache)));
              self.skipWaiting();
            });
            self.addEventListener('activate', e => { e.waitUntil(self.clients.claim()); });
            self.addEventListener('fetch', e => {
              e.respondWith(
                fetch(e.request).then(r=>r).catch(() => caches.match(e.request).then(r=>r||caches.match(location.href)))
              );
            });
          `;
          try {
            const blob = new Blob([swCode], {type: 'application/javascript'});
            const swUrl = URL.createObjectURL(blob);
            navigator.serviceWorker.register(swUrl);
          } catch {}
        }
      }
  
      // --- INIT ---
      function init() {
        loadNotes();
        renderCategoryFilterDropdown();
        renderNoteCategoryDropdown("all");
        updateNoteList();
        setupEventListeners();
        registerServiceWorkerInline();
      }
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
      else init();
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>