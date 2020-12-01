import { createUser } from "./APIConnector.js";
const form = document.getElementById("formCadastro");
form.addEventListener("submit", async (e) => {
  e.preventDefault();
  const name = document.getElementById("name").value;
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  if (await createUser(name, email, password)) {
    location.href = "./index.html";
  }
});