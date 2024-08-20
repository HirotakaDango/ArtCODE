// Get the theme toggle button, icon element, and html element
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const htmlElement = document.documentElement;

// Check if the user's preference is stored in localStorage
const savedTheme = localStorage.getItem('theme');
if (savedTheme) {
  htmlElement.setAttribute('data-bs-theme', savedTheme);
  updateThemeIcon(savedTheme);
}

// Add an event listener to the theme toggle button
themeToggle.addEventListener('click', () => {
  // Toggle the theme
  const currentTheme = htmlElement.getAttribute('data-bs-theme');
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

  // Apply the new theme
  htmlElement.setAttribute('data-bs-theme', newTheme);
  updateThemeIcon(newTheme);

  // Store the user's preference in localStorage
  localStorage.setItem('theme', newTheme);
});

// Function to update the theme icon
function updateThemeIcon(theme) {
  if (theme === 'dark') {
    themeIcon.classList.remove('bi-moon-fill');
    themeIcon.classList.add('bi-sun-fill');
  } else {
    themeIcon.classList.remove('bi-sun-fill');
    themeIcon.classList.add('bi-moon-fill');
  }
}