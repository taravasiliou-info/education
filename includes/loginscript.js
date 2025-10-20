

/**
 * Hides the login form and displays the registration form.
 * Prevents the default action of a link click such as navigating to a URL.
 */
function displayRegisterForm(event) {
  event.preventDefault(); // Prevent the link from navigating
  document.getElementById("loginForm").style.display = "none"; // Hide login form
  document.getElementById("registerForm").style.display = "block"; // Show registration form
}

/**
 * Displays the login form and hides the registration form.
* Prevents the default action of a link click such as navigating to a URL.
 */
function displayLoginForm(event) {
  event.preventDefault(); // Stop the link from navigating
  document.getElementById("registerForm").style.display = "none"; // Hide registration form
  document.getElementById("loginForm").style.display = "block"; // Show login form
}

/**
 * Makes sure that the passwords match.
 */

  function checkPasswords() {
    const password = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (password !== confirmPassword) {
      alert('Passwords do not match.');
      return false; // Prevent form submission
    }

    alert('Form Submitted');
    return true;
  }

  
  