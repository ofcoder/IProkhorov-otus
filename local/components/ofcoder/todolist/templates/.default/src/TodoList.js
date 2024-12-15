import "./TodoList.scss";

import {
  PreventDefault,
  ajax,
  append,
  bind,
  cleanNode,
  create,
  createFragment,
  data,
  findChildren,
  fireEvent,
  message,
} from "main.core";

/**Подключаем наш экстеншен с нотификациями который сделали ранее */
import { Notification } from "myextensions.notification";

/**Так же создаем класс */
export class TodoList {
  constructor() {
    /**
     * Function provides access to language messages.
     * If the param parameter is a string, the language message with the identifier param will be returned.
     * Otherwise, the parameter is interpreted as an object of the form {identifier: message, identifier: message}
     * language messages are added to the current ones (overwriting existing ones).cd
     *
     * @param {string|Object} param - The identifier or an object of language messages.
     * @returns {string} - The language message with the given identifier.
     * @example <caption>
     *
     * BX.message({test: 123}) //returns undefined
     *
     * BX.message('test') //returns 123
     * </caption>
     */
    this.signedParameters = message("signedParameters");

    this.todoList = document.getElementById("todo__list");
    this.filter = document.getElementById("todo__filter");
    this.addForm = document.getElementById("todo__add-form");
    /**Выносим накидывание ивентов в отдельную функцию, т.к. наши todo задачи, будут динамическими */
    this.initTaskEvent();

    /**
     * Binds an event handler to an element or a collection of elements.
     * @param {Node} el - The element to bind the event handler to.
     * @param {String} event - The name of the event to bind the handler to.
     * @param {Function} handler - The function to execute when the event is triggered.
     */
    bind(this.filter, "submit", (e) => this.submitFilter(e, this));
    bind(this.addForm, "submit", (e) => this.submitAddForm(e, this));
  }
  /**Накидываем события на наши задачи. */
  initTaskEvent() {
    /**
     * Finds all direct child nodes of the specified DOMNode.
     *
     * @param {Node} node - The DOMNode to search for children.
     * @param {Object} [params] - An optional object containing parameters to filter the child nodes.",
     * @param {string} [params.tagName] - The tag name of the desired child node.",
     * @param {string} [params.className] - The class name of the desired child node.",
     * @param {Object} [params.attrs] - An object containing attribute key-value pairs to filter the child nodes.",
     * @param {Object} [params.props] - An object containing property key-value pairs to filter the child nodes.",
     * @param {boolean} [recursive=false] - If true, also search for children of the children.
     * @returns {array<Node>} - An array of all direct child nodes.
     */
    /**Получаем все наши задачи */
    const tasks = findChildren(this.todoList, { tagName: "input" }, true);

    tasks.forEach((task) => {
      const taskId = data(task, "id");
      /**Используем здесь замыкание чтобы когда человек менял data-id, значение все равно приходило на бэкэнд старое */
      bind(task, "change", (e) =>
        this.changeTaskStatus(taskId, e.target.checked, this)
      );
    });
  }
  /**Отправка форму фильтра на бэкэнд чтобы получить задачи по фильтру */
  async submitFilter(e, classContext) {
    /**
     * Prevents the default action of an event from occurring.
     * @param {Event} e - The event object.
     */
    PreventDefault(e);
    /**
     * This function makes an AJAX request to a component.
     * @param {string} componentName - The name of the component. Example: 'mysitetemplate:mycomponent
     * @param {string} action - The name of the function in ajax.php or class.php .
     * @param {Object} [params] - The parameters for the request.
     * @param {string} [params.mode] - The mode for the request. Can be either "class" or "ajax".
     * @param {string} [params.method] - default POST
     * @param {Object|FormData} [params.data] - The data for the request.
     * @param {boolean} [params.json] - A flag indicating whether to send JSON data. In this case, when the request is sent, the Content-type header
     * will be set to application/json, and the controllers will be able to access the original JSON, which will make it easier to work with numbers and empty values.
     * @param {Object} [params.navigation] - The navigation data for the request.
     * @param {string|Object} [params.analyticsLabel] - The analytics label for the request. It is used as a marker for analytics to indicate popular content.
     * @param {string} [params.signedParameters] - The signed parameters for the request.
     * @returns {Promise} - A promise that resolves with the response from the server.
     */
    /**Если на бэкэнде будет error он автоматически попадет в catch*/
    try {
      const { data } = await ajax.runComponentAction(
        "ofcoder:todolist",
        "getFilteredTasks",
        {
          mode: "class",
          data: new FormData(classContext.filter),
          signedParameters: classContext.signedParameters,
        }
      );
      /**Получаем наши задачи и рендерим их */
      const template = classContext.renderTasks(data);

      /**
       * Cleans the specified node and its descendants.
       * @param {Node} node - The node to clean.
       * @param {boolean} [removeSelf=false] - Indicates whether to remove the node itself.
       */
      /**Очищаем содержимое нашего списка */
      cleanNode(classContext.todoList);
      /**
       * Appends a node to the end of another node.
       *
       * @param {Node} node - The node to be appended.
       * @param {Node} dstNode - The destination node where the node will be appended.
       */
      /**Вставляем наше значение */
      append(template.content, classContext.todoList);
      /**Заново иницилизируем обработчики на задачах */
      classContext.initTaskEvent();
    } catch ({ errors }) {
      /**Выводим ошибку пользователю на экран если что то сломалось */
      errors.forEach((error) => new Notification(error.message));
    }
  }
  async submitAddForm(e, classContext) {
    PreventDefault(e);
    try {
      const { data } = await ajax.runComponentAction(
        "ofcoder:todolist",
        "addTask",
        {
          mode: "class",
          data: new FormData(classContext.addForm),
          signedParameters: classContext.signedParameters,
        }
      );
      new Notification(data);
    } catch ({ errors }) {
      errors.forEach((error) => new Notification(error.message));
    }
  }
  /**Отправляем данные на бэкэнд что статус задания изменился */
  async changeTaskStatus(taskId, taskCompleted, classContext) {
    try {
      await ajax.runComponentAction("ofcoder:todolist", "changeTaskStatus", {
        mode: "class",
        data: {
          taskId: taskId,
          taskCompleted: taskCompleted,
        },
        signedParameters: classContext.signedParameters,
      });
    } catch ({ errors }) {
      errors.forEach((error) => new Notification(error.message));
    }
  }
  /**Создание template с нашими задачами */
  renderTasks(tasks) {
    const html = tasks
      .map((task) => {
        return /*html*/ `
        <li class="todo__item">
          <label>
            <input
              type="checkbox"
              ${Number(task["UF_COMPLETED"]) && "checked"}
              data-id="${task["ID"]}"
            />
            ${task["UF_TEXT"]}
          </label>
        </li>
      `;
      })
      .join(" ");
    return create("template", { html: html });
  }
}