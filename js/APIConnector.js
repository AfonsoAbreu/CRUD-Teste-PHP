export async function createUser (name, email, password) {
  const response = await fetch('http://localhost/CRUD-Teste-PHP/API/user', {
    method: 'POST',
    headers: new Headers({"Content-Type": "application/json"}),
    body: JSON.stringify({
      username: name,
      email: email,
      password: password
    })
  });
  return (response.status === 200);
}

export async function login (email, password) {
  const response = await fetch('http://localhost/CRUD-Teste-PHP/API/auth', {
    method: 'POST',
    headers: new Headers({"Content-Type": "application/json"}),
    body: JSON.stringify({
      email: email,
      password: password
    })
  });
  const responseContent =  await response.text();
  if (response.status === 200) {
    localStorage.setItem("token", responseContent);
    return true;
  }
  return false;
}

export async function deleteUser () {
  const response = await fetch('http://localhost/CRUD-Teste-PHP/API/user', {
    method: 'DELETE',
    headers: new Headers({"Content-Type": "application/json"}),
    body: JSON.stringify({
      token: localStorage.getItem("token")
    })
  });
  return (response.status === 200);
}

export async function addCar (makeId, model, color, year, numberPlate) {
  const response = await fetch('http://localhost/CRUD-Teste-PHP/API/car', {
    method: 'POST',
    headers: new Headers({"Content-Type": "application/json"}),
    body: JSON.stringify({
      makeId: makeId,
      model: model,
      color: color,
      year: year,
      numberPlate: numberPlate,
      token: localStorage.getItem("token")
    })
  });
  return (response.status === 200);
}

export async function listCars (page) {
  const response = await fetch(`http://localhost/CRUD-Teste-PHP/API/car-list`, {
    method: 'POST',
    headers: new Headers({"Content-Type": "application/json"}),
    body: JSON.stringify({
      page: page,
      token: localStorage.getItem("token")
    })
  });
  return await response.json();
}

export async function removeCar (carId) {
  const response = await fetch("http://localhost/CRUD-Teste-PHP/API/car", {
    method: 'DELETE',
    headers: new Headers({"Content-Type": "application/json"}),
    body: JSON.stringify({
      carId: carId,
      token: localStorage.getItem("token")
    })
  });
  return (response.status === 200);
}

export async function getMakes () {
  const response = await fetch("http://localhost/CRUD-Teste-PHP/API/make", {
    method: 'GET',
    headers: new Headers({"Content-Type": "application/json"})
  });
  return await response.json();
}