document.addEventListener("DOMContentLoaded", function () {
    document.body.innerHTML += '<div class="overlay"></div><div class="loader"></div>';
  });
  
  // Remove the loading animation when the page is fully loaded
  window.addEventListener("load", function () {
    var loader = document.querySelector(".loader");
    var overlay = document.querySelector(".overlay");
  
    // Fade out the loading animation and overlay
    loader.style.opacity = 0;
    overlay.style.opacity = 0;
  
    // Remove the elements from the DOM after the animation is complete
    setTimeout(function () {
      loader.remove();
      overlay.remove();
    }, 1000); // You can adjust the delay as needed (milliseconds)
  });