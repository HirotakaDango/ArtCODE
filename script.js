// script.js

// Add event listener for button click
document.querySelector('.favorite-btn').addEventListener('click', function(event) {
  event.preventDefault();

  // Send AJAX request to favorite.php
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'favorite.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      // Update button text based on response from favorite.php
      var buttonText = xhr.responseText.trim();
      var button = document.querySelector('.favorite-btn button');
      button.textContent = buttonText;
    }
  };
  xhr.send('image_id=<?php echo $image['id']; ?>');
});
