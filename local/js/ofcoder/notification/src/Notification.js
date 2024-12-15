//https://habr.com/ru/articles/815439/

//Подключение SCSS чтобы он собирался
//выключать при тесте bitrix test
import "./Notification.scss";

//Импорт функций из BX
import {
  append,
  create,
  findChildrenByClassName,
  findParent,
  insertBefore,
  remove,
} from "main.core";

//Экспортируем функцию чтобы потом её можно было вызывать через BX.Notification

export class Notification {
  constructor(message, status) {
    this.notificationsList =
      this.getNotificationsList() || this.buildNotificationsList();
    this.buildNotification(message, status);
  }
  getNotificationsList() {
    return document.getElementById("notifications-list");
  }
  buildNotificationsList() {
    const fragment = create("ul", {
      attrs: {
        id: "notifications-list",
        className: "notifications-list",
      },
      children: [
        create("button", {
          attrs: {
            type: "button",
            id: "notifications-list__close-all",
          },
          events: {
            //Вешаем обработчик событий на кнопку чтобы удалить все уведомления
            click: ({ target }) => {
              const parent = findParent(target);
              const notifications = findChildrenByClassName(
                parent,
                "notification"
              );

              notifications.forEach((notification) => remove(notification));
            },
          },
          text: "Закрыть все",
        }),
      ],
    });
    append(fragment, document.body);
    return this.getNotificationsList();
  }

  buildNotification(message, status) {
    const notification = create("li", {
      attrs: {
        className: `notification ${status ? "notification_" + status : ""}`,
      },
      children: [
        create("p", {
          attrs: {
            className: "notification__message",
          },
          text: message,
        }),
        create("button", {
          attrs: {
            type: "button",
            className: "notification__close",
          },
          text: "x",
          events: {
            //Вешаем обработчик на кнопку, чтобы удалить уведомление.
            click: ({ target }) => remove(findParent(target)),
          },
        }),
      ],
    });
    this.mountNotification(notification);
  }
  mountNotification(notification) {
    insertBefore(
      notification,
      document.getElementById("notifications-list__close-all")
    );
  }
}