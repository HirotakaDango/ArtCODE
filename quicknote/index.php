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
  
      .paragraph-counter {
        font-size: 0.75rem;
        padding: 2em;
        text-wrap: nowrap;
        font-weight: bold;
        margin-top: -45px;
        color: wwhite;
        position: absolute;
        text-align: center;
        min-width: 35px;
        user-select: none;
        z-index: 10;
      }
  
      .word-counter-container {
        position: absolute;
        left: 0;
        top: 44px;
        width: 80px;
        height: calc(100svh - 44px);
        overflow-y: auto;
        background-color: rgba(33, 37, 41, 0.5);
        padding: 10px 0;
        /* Hide scrollbar but keep functionality */
        scrollbar-width: none;
        /* Firefox */
        -ms-overflow-style: none;
        /* IE and Edge */
      }
  
      /* Hide scrollbar for Chrome, Safari and Opera */
      .word-counter-container::-webkit-scrollbar {
        display: none;
      }
    </style>
  </head>
  
  <body>
    <?php include('../header.php'); ?>
    <div class="w-100">
      <!-- All Notes View -->
      <div class="container mb-5 mt-3" id="allNotesView">
        <input id="searchInput" class="form-control rounded-pill mb-3 w-100" type="text" placeholder="🔍 Search..." />
        <!-- Categories Navigation -->
        <ul class="nav nav-tabs my-3">
          <select id="sortSelect" class="form-select border-0 fw-medium focus-ring focus-ring-dark" style="width: auto;">
            <option value="newest">Newest</option>
            <option value="oldest">Oldest</option>
          </select>
          <li class="nav-item ms-auto">
            <a class="nav-link active" href="#" id="allNotesTab"><i class="bi bi-card-text"></i></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" id="favoritesTab"><i class="bi bi-star-fill"></i></a>
          </li>
          <div class="dropdown">
            <button class="btn border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu rounded-4">
              <li>
                <button id="newNoteBtn" class="dropdown-item">
                  <i class="bi bi-plus-circle"></i> New Note
                </button>
              </li>
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
        <div id="selectedNotesActions" class="mb-3" style="display: none;">
          <div class="d-flex justify-content-between align-items-center">
            <div class="select-all-container align-items-center">
              <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
              <label for="selectAllCheckbox" class="mx-2">Select All</label>
            </div>
            <button id="deleteSelectedBtn" class="btn btn-danger btn-sm ms-2">
              <i class="bi bi-trash3"></i> Delete Selected (<span id="selectedCount">0</span>)
            </button>
          </div>
        </div>
  
        <div id="noteList" class="list-group">
          <!-- Notes will be listed here -->
        </div>
      </div>
  
      <!-- Single Note View -->
      <div id="singleNoteView" style="display: none;">
        <div class="container d-flex justify-content-between align-items-center bg-dark-subtle py-1">
          <div class="d-flex gap-3">
            <button id="backBtn" class="btn border-0">
              <i class="bi bi-chevron-left link-body-emphasis" style="-webkit-text-stroke: 2px;"></i>
            </button>
            <!-- Undo/Redo Buttons -->
            <button id="undoBtn" class="btn border-0">
              <i class="bi bi-arrow-counterclockwise"></i>
            </button>
          </div>
          <h6 id="noteDate" class="text-muted mx-auto my-auto"></h6>
          <div class="d-flex gap-3">
            <button id="redoBtn" class="btn border-0">
              <i class="bi bi-arrow-clockwise"></i>
            </button>
            <button id="deleteSingleBtn" class="btn border-0">
              <i class="bi bi-trash3-fill link-body-emphasis"></i>
            </button>
          </div>
        </div>
        <h2 id="noteTitle" class="d-none"></h2>
        <!-- Paragraph word counter (only visible on desktop) -->
        <div id="wordCounterContainer" class="word-counter-container d-none d-md-block">
          <!-- Word counters will be added here dynamically -->
        </div>
        <textarea id="noteContent"
          style="height: calc(100svh - 44px); max-height: calc(100svh - 44px); min-height: calc(100svh - 44px); box-sizing: border-box;"
          class="container form-control rounded-0 p-4 border-0 bg-dark-subtle focus-ring focus-ring-dark"></textarea>
      </div>
    </div>
  
    <input type="file" id="importInput" style="display: none;" accept=".json" />
  
    <!-- Word Counter (initially hidden) -->
    <div id="wordCounter" class="position-fixed bottom-0 end-0 fw-medium rounded-end-0 rounded-bottom-0 rounded-top-3 rounded-end-0 p-1" style="display: none; font-size: 12px;">Words: 0</div>
  
    <script>
      const allNotesView = document.getElementById('allNotesView');
      const singleNoteView = document.getElementById('singleNoteView');
      const newNoteBtn = document.getElementById('newNoteBtn');
      const sortSelect = document.getElementById('sortSelect');
      const noteList = document.getElementById('noteList');
      const noteTitle = document.getElementById('noteTitle');
      const noteDate = document.getElementById('noteDate');
      const noteContent = document.getElementById('noteContent');
      const deleteSingleBtn = document.getElementById('deleteSingleBtn');
      const backBtn = document.getElementById('backBtn');
      const searchInput = document.getElementById('searchInput');
      const exportBtn = document.getElementById('exportBtn');
      const importBtn = document.getElementById('importBtn');
      const importInput = document.getElementById('importInput');
      const allNotesTab = document.getElementById('allNotesTab');
      const favoritesTab = document.getElementById('favoritesTab');
      const undoBtn = document.getElementById('undoBtn');
      const redoBtn = document.getElementById('redoBtn');
      const wordCounter = document.getElementById('wordCounter');
      const wordCounterContainer = document.getElementById('wordCounterContainer');
      const selectedNotesActions = document.getElementById('selectedNotesActions');
      const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
      const selectAllCheckbox = document.getElementById('selectAllCheckbox');
      const selectedCount = document.getElementById('selectedCount');
  
      let notes = {};
      let currentNote = null;
      let autoSaveTimeout = null;
      // For undo/redo functionality
      let undoStack = [];
      let redoStack = [];
      let lastRecordedContent = '';
      // Selected notes for bulk deletion
      let selectedNotes = new Set();
      // Store paragraph positions for word counters
      let paragraphPositions = [];
      // Filtered notes for select all functionality
      let filteredNoteIds = [];
  
      // Current category: "all" or "favorites"
      let currentCategory = 'all';
  
      function htmlSpecialChars(str) {
        if (!str) return '';
        return str.replace(/[&<>"']/g, function (match) {
          switch (match) {
            case '&':
              return '&amp;';
            case '<':
              return '&lt;';
            case '>':
              return '&gt;';
            case '"':
              return '&quot;';
            case "'":
              return '&#039;';
          }
        });
      }
  
      function loadNotes() {
        const savedNotes = localStorage.getItem('notes');
        if (savedNotes) {
          try {
            notes = JSON.parse(savedNotes);
          } catch (e) {
            console.error('Error parsing saved notes:', e);
            notes = {};
          }
        }
      }
  
      function saveNotes() {
        localStorage.setItem('notes', JSON.stringify(notes));
      }
  
      function saveNote() {
        if (currentNote && notes[currentNote]) {
          const currentContent = noteContent.value || '';
          notes[currentNote].content = currentContent;
          const titleLine = currentContent.split('\n')[0] || 'Untitled';
          notes[currentNote].title = titleLine;
          notes[currentNote].date = new Date().toISOString();
          saveNotes();
          noteTitle.textContent = htmlSpecialChars(notes[currentNote].title);
          noteDate.textContent = new Date(notes[currentNote].date).toLocaleDateString('en-US');
          updateNoteList();
          updateTotalWordCounter();
          updateParagraphWordCounters(); // Update paragraph counters after saving
        }
      }
  
      function createNote() {
        const noteId = Date.now().toString();
        notes[noteId] = {
          title: 'New Note',
          content: '',
          date: new Date().toISOString(),
          favorite: false,
        };
        currentNote = noteId;
        saveNotes();
        showSingleNote();
        updateTotalWordCounter(); // Reset counter for new note
        updateParagraphWordCounters(); // Initialize paragraph counters
      }
  
      function deleteNote() {
        if (currentNote && notes[currentNote]) {
          if (confirm('Are you sure you want to delete this note?')) {
            delete notes[currentNote];
            saveNotes();
            currentNote = null;
            showAllNotes();
          }
        }
      }
  
      function deleteNoteFromList(noteId) {
        if (notes[noteId] && confirm('Are you sure you want to delete this note?')) {
          delete notes[noteId];
          // Also remove from selected notes if it was selected
          selectedNotes.delete(noteId);
          saveNotes();
          updateNoteList();
          updateSelectedNotesUI();
          if (noteId === currentNote) {
            showAllNotes();
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
  
      function showAllNotes() {
        allNotesView.style.display = 'block';
        singleNoteView.style.display = 'none';
        currentNote = null;
        updateNoteList();
        // Hide word counters on home view
        if (wordCounter) {
          wordCounter.style.display = 'none';
        }
        // Show selected notes actions if any are selected
        updateSelectedNotesUI();
      }
  
      function showSingleNote() {
        allNotesView.style.display = 'none';
        singleNoteView.style.display = 'block';
        // Display the word counter only in single note view
        if (wordCounter) {
          wordCounter.style.display = 'block';
        }
        if (currentNote && notes[currentNote]) {
          noteTitle.textContent = htmlSpecialChars(notes[currentNote].title);
          noteDate.textContent = new Date(notes[currentNote].date).toLocaleDateString('en-US');
          noteContent.value = notes[currentNote].content || '';
          // Add left margin to the textarea when in desktop view (for the counter)
          noteContent.classList.add('note-content-with-counter');
          // Reset undo/redo stacks when opening a note
          undoStack = [];
          redoStack = [];
          lastRecordedContent = noteContent.value;
          updateTotalWordCounter();
          setTimeout(() => {
            calculateParagraphPositions();
            updateParagraphWordCounters(); // Initialize paragraph counters
          }, 100);
        }
      }
  
      // Helper function to copy text with fallback (prevent scrolling)
      function copyTextToClipboard(text) {
        if (!text) text = '';
  
        if (navigator.clipboard && window.isSecureContext) {
          return navigator.clipboard.writeText(text);
        } else {
          const textArea = document.createElement("textarea");
          textArea.value = text;
          textArea.style.position = "fixed";
          textArea.style.top = "0";
          textArea.style.left = "0";
          textArea.style.width = "1px";
          textArea.style.height = "1px";
          textArea.style.padding = "0";
          textArea.style.border = "none";
          textArea.style.outline = "none";
          textArea.style.boxShadow = "none";
          textArea.style.background = "transparent";
          document.body.appendChild(textArea);
          if (typeof textArea.focus === "function") {
            try {
              textArea.focus({preventScroll: true});
            } catch (e) {
              textArea.focus();
            }
          } else {
            textArea.focus();
          }
          textArea.select();
          return new Promise((resolve, reject) => {
            try {
              const successful = document.execCommand('copy');
              document.body.removeChild(textArea);
              successful ? resolve() : reject(new Error("Copy command was unsuccessful"));
            } catch (err) {
              document.body.removeChild(textArea);
              reject(err);
            }
          });
        }
      }
  
      function updateNoteList() {
        if (!noteList) return;
  
        noteList.innerHTML = '';
  
        const query = searchInput.value.toLowerCase();
        let filteredNotes = Object.entries(notes).filter(([id, note]) =>
          (note.title && note.title.toLowerCase().includes(query)) ||
          (note.content && note.content.toLowerCase().includes(query))
        );
        if (currentCategory === 'favorites') {
          filteredNotes = filteredNotes.filter(([id, note]) => note.favorite);
        }
        const sortedNotes = filteredNotes.sort((a, b) => {
          const dateA = new Date(a[1].date);
          const dateB = new Date(b[1].date);
          return sortSelect.value === 'newest' ? dateB - dateA : dateA - dateB;
        });
  
        // Store filtered note IDs for "Select All" functionality
        filteredNoteIds = sortedNotes.map(([id]) => id);
  
        sortedNotes.forEach(([noteId, note]) => {
          const card = document.createElement('div');
          card.className = 'card bg-body-tertiary mb-3 note-card border-0 rounded-3 ps-5 position-relative';
          card.style.cursor = 'pointer';
          card.style.position = 'relative';
  
          // Add checkbox for note selection
          const checkboxContainer = document.createElement('div');
          checkboxContainer.className = 'position-absolute top-50 start-0 p-0 translate-middle-y ms-4';
  
          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.className = 'form-check-input note-checkbox';
          checkbox.checked = selectedNotes.has(noteId);
          checkbox.addEventListener('change', (e) => {
            e.stopPropagation();
            if (checkbox.checked) {
              selectedNotes.add(noteId);
            } else {
              selectedNotes.delete(noteId);
              // Uncheck "Select All" if any individual note is unchecked
              if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
              }
            }
            updateSelectedNotesUI();
          });
  
          checkboxContainer.appendChild(checkbox);
          card.appendChild(checkboxContainer);
  
          // Main card content
          const cardBody = document.createElement('div');
          cardBody.className = 'card-body';
  
          const noteHeader = document.createElement('div');
          noteHeader.className = 'note-header';
  
          const dateElement = document.createElement('small');
          dateElement.className = 'fw-medium';
          dateElement.textContent = new Date(note.date).toLocaleDateString('en-US');
  
          const buttonsDiv = document.createElement('div');
  
          const copyBtn = document.createElement('button');
          copyBtn.className = 'action-btn copy-btn';
          copyBtn.innerHTML = '<i class="bi bi-copy"></i>';
  
          const downloadBtn = document.createElement('button');
          downloadBtn.className = 'action-btn download-btn';
          downloadBtn.innerHTML = '<i class="bi bi-download"></i>';
  
          const favoriteBtn = document.createElement('button');
          favoriteBtn.className = 'action-btn favorite-btn';
          favoriteBtn.innerHTML = `<i class="bi ${note.favorite ? 'bi-star-fill' : 'bi-star'}"></i>`;
  
          const deleteBtn = document.createElement('button');
          deleteBtn.className = 'action-btn delete-btn';
          deleteBtn.innerHTML = '<i class="bi bi-trash3-fill"></i>';
  
          buttonsDiv.appendChild(copyBtn);
          buttonsDiv.appendChild(downloadBtn);
          buttonsDiv.appendChild(favoriteBtn);
          buttonsDiv.appendChild(deleteBtn);
  
          noteHeader.appendChild(dateElement);
          noteHeader.appendChild(buttonsDiv);
  
          const contentDiv = document.createElement('div');
          contentDiv.className = 'mt-3';
          const safeContent = note.content || '';
          contentDiv.textContent = safeContent.substring(0, 200) + (safeContent.length > 200 ? '...' : '');
  
          cardBody.appendChild(noteHeader);
          cardBody.appendChild(contentDiv);
          card.appendChild(cardBody);
  
          card.addEventListener('click', (e) => {
            if (!e.target.closest('button') && !e.target.closest('input')) {
              currentNote = noteId;
              showSingleNote();
            }
          });
  
          copyBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            copyTextToClipboard(note.content || '')
              .then(() => {
                const copyIndicator = document.createElement('span');
                copyIndicator.textContent = 'Copied!';
                copyIndicator.style.position = 'absolute';
                copyIndicator.style.top = '10px';
                copyIndicator.style.right = '10px';
                copyIndicator.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
                copyIndicator.style.color = 'white';
                copyIndicator.style.padding = '5px 10px';
                copyIndicator.style.borderRadius = '5px';
                copyIndicator.style.fontSize = '0.9em';
                card.appendChild(copyIndicator);
                setTimeout(() => {
                  copyIndicator.remove();
                }, 2000);
              })
              .catch(err => {
                console.error('Failed to copy note content: ', err);
              });
          });
  
          downloadBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const content = note.content || '';
            let firstSentence = (content.split('.')[0] || '').trim();
            if (!firstSentence) {
              firstSentence = "Untitled";
            }
            let words = firstSentence.split(/\s+/).slice(0, 4).join(' ');
            let defaultName = words || "Untitled";
            let customName = prompt("Enter filename:", defaultName);
            if (customName === null) {
              return;
            }
            const filename = `${customName}.txt`;
            const blob = new Blob([content], {type: 'text/plain;charset=utf-8'});
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = filename;
            a.click();
            URL.revokeObjectURL(a.href); // Clean up to avoid memory leaks
          });
  
          favoriteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleFavorite(noteId);
          });
  
          deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            deleteNoteFromList(noteId);
          });
  
          noteList.appendChild(card);
        });
  
        updateSelectedNotesUI();
      }
  
      // Handle bulk delete for selected notes
      function updateSelectedNotesUI() {
        if (!selectedNotesActions || !selectedCount) return;
  
        // Always show the actions bar if we have any notes at all
        if (Object.keys(notes).length > 0) {
          selectedNotesActions.style.display = 'block';
        } else {
          selectedNotesActions.style.display = 'none';
        }
  
        // Update the selected count
        selectedCount.textContent = selectedNotes.size;
  
        // Update "Select All" checkbox state
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
          selectedNotes.forEach((noteId) => {
            delete notes[noteId];
          });
          saveNotes();
          selectedNotes.clear();
          updateSelectedNotesUI();
          updateNoteList();
        }
      }
  
      // Total word counter update function
      function updateTotalWordCounter() {
        if (!wordCounter || !noteContent) return;
  
        const text = noteContent.value || '';
        const words = text.trim().split(/\s+/).filter(word => word.length > 0);
        wordCounter.textContent = 'Words: ' + words.length;
      }
  
      // Calculate the actual positions of paragraphs in the textarea
      function calculateParagraphPositions() {
        if (!noteContent) return;
  
        paragraphPositions = [];
        const text = noteContent.value || '';
        const paragraphs = text.split('\n\n');
  
        let charOffset = 0;
        paragraphs.forEach((paragraph, index) => {
          if (paragraph.trim() !== '') {
            try {
              const position = getCaretCoordinates(noteContent, charOffset);
              paragraphPositions.push({
                index: index + 1,
                top: position.top,
                wordCount: paragraph.trim().split(/\s+/).filter(word => word.length > 0).length
              });
            } catch (e) {
              console.error('Error calculating paragraph position:', e);
            }
          }
          charOffset += paragraph.length + 2; // +2 for '\n\n'
        });
      }
  
      // Paragraph word counter implementation
      function updateParagraphWordCounters() {
        if (!wordCounterContainer) return;
  
        // Clear previous counters
        wordCounterContainer.innerHTML = '';
  
        if (!currentNote) return;
  
        calculateParagraphPositions();
  
        paragraphPositions.forEach((para) => {
          // Create counter element
          const counter = document.createElement('div');
          counter.className = 'paragraph-counter';
          counter.textContent = `P${para.index}: ${para.wordCount}`;
          counter.style.top = `${para.top}px`;
  
          wordCounterContainer.appendChild(counter);
        });
      }
  
      // Helper function to get exact coordinates of caret position
      // This is a simplified version of textarea-caret-position library
      function getCaretCoordinates(element, position) {
        if (!element) {
          throw new Error('Element is required for getCaretCoordinates');
        }
  
        const div = document.createElement('div');
        const style = div.style;
        const computed = window.getComputedStyle(element);
  
        style.whiteSpace = 'pre-wrap';
        style.wordWrap = 'break-word';
        style.position = 'absolute';
        style.visibility = 'hidden';
        style.width = `${element.clientWidth}px`;
        style.fontSize = computed.fontSize;
        style.fontFamily = computed.fontFamily;
        style.fontWeight = computed.fontWeight;
        style.lineHeight = computed.lineHeight;
        style.padding = computed.padding;
        style.border = computed.border;
        style.boxSizing = computed.boxSizing;
  
        const text = element.value.substring(0, position) || '';
        const span = document.createElement('span');
        span.textContent = element.value.substring(position) || '.';
  
        div.textContent = text;
        div.appendChild(span);
  
        document.body.appendChild(div);
        const coordinates = {
          top: span.offsetTop + parseInt(computed.borderTopWidth) + parseInt(computed.paddingTop),
          left: span.offsetLeft + parseInt(computed.borderLeftWidth) + parseInt(computed.paddingLeft),
          height: parseInt(computed.lineHeight)
        };
        document.body.removeChild(div);
  
        return coordinates;
      }
  
      // Event Listener Setup
      function setupEventListeners() {
        // Category tab event listeners
        if (allNotesTab) {
          allNotesTab.addEventListener('click', (e) => {
            e.preventDefault();
            currentCategory = 'all';
            allNotesTab.classList.add('active');
            if (favoritesTab) {
              favoritesTab.classList.remove('active');
            }
            updateNoteList();
          });
        }
  
        if (favoritesTab) {
          favoritesTab.addEventListener('click', (e) => {
            e.preventDefault();
            currentCategory = 'favorites';
            favoritesTab.classList.add('active');
            if (allNotesTab) {
              allNotesTab.classList.remove('active');
            }
            updateNoteList();
          });
        }
  
        // Textarea event listeners
        if (noteContent) {
          // Basic Undo/Redo implementation
          noteContent.addEventListener('input', () => {
            if (noteContent.value !== lastRecordedContent) {
              undoStack.push(lastRecordedContent);
              lastRecordedContent = noteContent.value;
              redoStack = [];
            }
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
              saveNote();
              updateParagraphWordCounters();
            }, 300);
            updateTotalWordCounter();
          });
  
          // Handle scroll sync between textarea and word counters
          noteContent.addEventListener('scroll', () => {
            // Update paragraph counters on scroll to handle any position changes
            const scrollTop = noteContent.scrollTop;
            const counters = document.querySelectorAll('.paragraph-counter');
            counters.forEach((counter) => {
              const counterTop = parseInt(counter.style.top);
              counter.style.transform = `translateY(-${scrollTop}px)`;
            });
          });
        }
  
        if (undoBtn) {
          undoBtn.addEventListener('click', () => {
            if (undoStack.length > 0) {
              const previousState = undoStack.pop();
              redoStack.push(noteContent.value);
              noteContent.value = previousState;
              lastRecordedContent = previousState;
              saveNote();
              updateTotalWordCounter();
              updateParagraphWordCounters();
            }
          });
        }
  
        if (redoBtn) {
          redoBtn.addEventListener('click', () => {
            if (redoStack.length > 0) {
              const nextState = redoStack.pop();
              undoStack.push(noteContent.value);
              noteContent.value = nextState;
              lastRecordedContent = nextState;
              saveNote();
              updateTotalWordCounter();
              updateParagraphWordCounters();
            }
          });
        }
  
        // Handle window resize events
        window.addEventListener('resize', () => {
          if (currentNote) {
            setTimeout(updateParagraphWordCounters, 100);
          }
        });
  
        // Handle "Select All" checkbox
        if (selectAllCheckbox) {
          selectAllCheckbox.addEventListener('change', () => {
            if (selectAllCheckbox.checked) {
              // Select all filtered notes
              filteredNoteIds.forEach(id => selectedNotes.add(id));
            } else {
              // Deselect all notes
              selectedNotes.clear();
            }
            updateNoteList();
            updateSelectedNotesUI();
          });
        }
  
        // Set up other button event listeners
        if (newNoteBtn) {
          newNoteBtn.addEventListener('click', createNote);
        }
  
        if (deleteSingleBtn) {
          deleteSingleBtn.addEventListener('click', deleteNote);
        }
  
        if (backBtn) {
          backBtn.addEventListener('click', showAllNotes);
        }
  
        if (sortSelect) {
          sortSelect.addEventListener('change', updateNoteList);
        }
  
        if (searchInput) {
          searchInput.addEventListener('input', updateNoteList);
        }
  
        if (exportBtn) {
          exportBtn.addEventListener('click', exportNotes);
        }
  
        if (importBtn && importInput) {
          importBtn.addEventListener('click', () => importInput.click());
          importInput.addEventListener('change', importNotes);
        }
  
        if (deleteSelectedBtn) {
          deleteSelectedBtn.addEventListener('click', deleteSelectedNotes);
        }
      }
  
      function exportNotes() {
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(notes));
        const downloadAnchorNode = document.createElement('a');
        downloadAnchorNode.setAttribute("href", dataStr);
        downloadAnchorNode.setAttribute("download", "notes_export.json");
        document.body.appendChild(downloadAnchorNode);
        downloadAnchorNode.click();
        downloadAnchorNode.remove();
      }
  
      function importNotes(event) {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function (e) {
            try {
              const importedNotes = JSON.parse(e.target.result);
              notes = {...notes, ...importedNotes};
              saveNotes();
              updateNoteList();
              alert('Notes imported successfully!');
            } catch (error) {
              alert('Error importing notes. Please make sure the file is a valid JSON export.');
            }
          };
          reader.readAsText(file);
        }
      }
  
      // Initialize app
      function initApp() {
        loadNotes();
        setupEventListeners();
        showAllNotes();
  
        // Set up service worker
        if ('serviceWorker' in navigator) {
          const swCode = `
              const CACHE_NAME = 'notetakeapp-v1';
              const urlsToCache = [
                location.href,
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'
              ];
              self.addEventListener('install', event => {
                event.waitUntil(
                  caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
                );
                self.skipWaiting();
              });
              self.addEventListener('activate', event => {
                event.waitUntil(self.clients.claim());
              });
              self.addEventListener('fetch', event => {
                event.respondWith(
                  fetch(event.request)
                    .then(response => response)
                    .catch(() => {
                      return caches.match(event.request)
                        .then(response => response || caches.match(location.href));
                    })
                );
              });
            `;
          try {
            const blob = new Blob([swCode], {type: 'application/javascript'});
            const swUrl = URL.createObjectURL(blob);
            navigator.serviceWorker.register(swUrl)
              .then(registration => {
                console.log('Service Worker registered with scope:', registration.scope);
              })
              .catch(error => {
                console.log('Service Worker registration failed:', error);
              });
          } catch (e) {
            console.error('Error registering service worker:', e);
          }
        }
      }
  
      // Start the app when DOM is fully loaded
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
      } else {
        initApp();
      }
    </script>
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>