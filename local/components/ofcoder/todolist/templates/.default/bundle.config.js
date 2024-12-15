module.exports = {
  input: "./src/TodoList.js",

  output: {
    js: "./script.js",
    css: "./style.css",
  },
  namespace: "BX",
  minification: true,
  treeshake: false,
};