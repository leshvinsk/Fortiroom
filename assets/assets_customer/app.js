const inputs = document.querySelectorAll(".input-field");
const toggle_btn = document.querySelectorAll(".toggle");
const toggle_carousel_btn = document.querySelectorAll(".toggle-carousel");
const data_btn = document.querySelectorAll(".data");
const main = document.querySelector("main");
const bullets = document.querySelectorAll(".bullets span");
const images = document.querySelectorAll(".image");

inputs.forEach((inp) => {
  inp.addEventListener("focus", () => {
    inp.classList.add("active");
  });
  inp.addEventListener("blur", () => {
    if (inp.value != "") return;
    inp.classList.remove("active");
  });
});

toggle_btn.forEach((btn) => {
  btn.addEventListener("click", () => {
    main.classList.toggle("sign-up-mode");
  });
});

toggle_carousel_btn.forEach((btn) => {
  btn.addEventListener("click", () => {
    main.classList.toggle("sign-up-mode");
  });
});

data_btn.forEach((btn) => {
  btn.addEventListener("click", () => {
    main.classList.toggle("forgot-password-mode");
  });
});

const loginForm = document.getElementById('login');
const registerForm = document.getElementById('register');
