import { listCars, addCar, removeCar, getMakes } from "./APIConnector.js";

let pagina = 1;
let carros;
let tableBody = document.getElementById("carListBody");

async function deleteCarro (id) {
  await removeCar(id);
  await mudarPagina(0, true);
}

async function mudarPagina (fator, flag = false) {
  let newcarros = await listCars(pagina+fator);
  if (newcarros === "Missing token") {
    sair();
  }
  if (newcarros.length !== 0 || flag) {
    pagina += fator;
    carros = newcarros;
    tableBody.innerHTML = "";
    for (let i = 0; i < carros.length; i++) {
      const linha = tableBody.insertRow();
      const fabricante = linha.insertCell();
      fabricante.appendChild(document.createTextNode(carros[i].makeName));

      const modelo = linha.insertCell();
      modelo.appendChild(document.createTextNode(carros[i].model));

      const cor = linha.insertCell();
      cor.appendChild(document.createTextNode(carros[i].color));

      const ano = linha.insertCell();
      ano.appendChild(document.createTextNode(carros[i].year));

      const placa = linha.insertCell();
      placa.appendChild(document.createTextNode(carros[i].numberPlate));

      const del = linha.insertCell();
      const delbutton = document.createElement("button");
      delbutton.addEventListener("click", () => deleteCarro(carros[i].id));
      delbutton.classList.add("button");
      const delico = document.createElement("i");
      delico.setAttribute("class", "fas fa-trash-alt");
      delbutton.appendChild(delico);
      del.appendChild(delbutton);
      del.classList.add("del");
    }
  }
}
mudarPagina(0);

const inserir = document.getElementById("insert");
inserir.addEventListener("click", insert);
const prev = document.getElementById("prev");
prev.addEventListener("click", () => mudarPagina(-1));
const next = document.getElementById("next");
next.addEventListener("click", () => mudarPagina(1));

const marcaSelect = document.createElement("select");
getMakes().then(e => {
  for (let i = 0; i < e.length; i++) {
    const opt = document.createElement("option");
    opt.text = e[i].name;
    marcaSelect.options.add(opt);
  }    
});

const modeloInput = document.createElement("input");
modeloInput.type = "text";
modeloInput.placeholder = "Modelo";

const corInput = document.createElement("input");
corInput.type = "text";
corInput.placeholder = "Cor";

const anoInput = document.createElement("input");
anoInput.type = "text";
anoInput.placeholder = "ano";

const placaInput = document.createElement("input");
placaInput.type = "text";
placaInput.placeholder = "placa";

const addBtn = document.createElement("button");
addBtn.classList.add("button");
addBtn.appendChild(document.createTextNode("+"));
addBtn.addEventListener("click", async () => {
  await addCar(marcaSelect.selectedIndex+1, modeloInput.value, corInput.value, anoInput.value, placaInput.value);
  
  tableBody.deleteRow(-1);
  inserir.disabled = false;
  prev.disabled = false;
  next.disabled = false;

  mudarPagina(0);
});

function insert () {
  inserir.disabled = true;
  prev.disabled = true;
  next.disabled = true;
  const linha = tableBody.insertRow();
  const marca = linha.insertCell();
  const marcaDesc = document.createTextNode("Marca: ");
  marca.appendChild(marcaDesc);
  marca.appendChild(marcaSelect);

  const modelo = linha.insertCell();
  modelo.appendChild(modeloInput);

  const cor = linha.insertCell();
  cor.appendChild(corInput);

  const ano = linha.insertCell();
  ano.appendChild(anoInput);

  const placa = linha.insertCell();
  placa.appendChild(placaInput);

  const add = linha.insertCell();
  add.appendChild(addBtn);
}

const sairbtn = document.getElementById("sair");
sairbtn.addEventListener("click", sair);
function sair () {
  location.href = "./index.html";
}