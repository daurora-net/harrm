document.addEventListener("DOMContentLoaded", function () {
  var menuToggle = document.getElementById("menu-toggle");
  var sideMenu = document.getElementById("side-menu");
  menuToggle.addEventListener("click", function () {
    sideMenu.classList.toggle("open");
  });
});

document.addEventListener("DOMContentLoaded", function () {
  var closeMenu = document.getElementById("close-menu");
  var sideMenu = document.getElementById("side-menu");
  closeMenu.addEventListener("click", function () {
    sideMenu.classList.remove("open");
  });
});