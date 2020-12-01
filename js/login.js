import { login } from "./APIConnector.js";
const form = document.getElementById("formLogin");
form.addEventListener("submit", async (e) => {
  e.preventDefault();
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  if (await login(email, password)) {
    location.href = "./bemvindo.html";
  }
});