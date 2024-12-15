module.exports = {
  input: "./src/doctors.js",
  output: "./dist/doctors.bundle.js",
  namespaces: 'BX.Odcoder.Doctors',
  plugins: {
    // Переопределяет параметры Babel.
    // Можно указать собственные параметры Babel
    // https://babeljs.io/docs/en/options
    // Если указать false, то код будет собран без транспиляции

    /*babel: boolean | Object,*/

    // Дополнительные плагины Rollup,
    // которые будут выполняться при сборке бандлов
    /*custom: Array<string | Function>,*/

    //В документации данное свойство не добавили
    //Данная опция позволяет разрешать использование плагинов или отключать
    //По дефолту false
    resolve: true
  },
      // Включает или отключает минификацию.
    // По умолчанию отключено.
    // Может принимать объект настроек Terser:
    // false — не минифицировать (по умолчанию)
    // true — минифицировать с настройками по умолчанию
    // object — минифицировать с указанными настройками
  minification: false, //boolean | object,

  // Allow or deny assembler to edit config.php
  // Default: true (allowed)
  adjustConfigPhp: true,
  // Allows or denies assembler to delete unused code .
  // Default: true (allowed).
  treeshake: true,
};