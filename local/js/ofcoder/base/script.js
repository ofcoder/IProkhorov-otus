//https://habr.com/ru/articles/810581/

document.addEventListener("DOMContentLoaded", () => {
  document.querySelector("body").innerHTML = "<h1 class='hello-world'>Hello world from ext</h1>";
});
//вариант для runtime
/*
document.querySelector("body").innerHTML =
  "<h1 class='hello-world'>Hello world from ext</h1>";
*/
const sumResult = sum(5, 2);
const multiplicationResult = multiplication(5, 2);

console.log(sum(1, 2));
console.log(multiplication(3, 3));