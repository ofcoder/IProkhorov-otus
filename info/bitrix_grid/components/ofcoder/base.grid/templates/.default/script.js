BX.namespace('Ofcoder.BaseGrid.List')

BX.Ofcoder.BaseGrid.List = {
  gridId: null,
  pathTemplates: {},

  init: function(params) {
    // @TODO проверка существования и корректности значений
    this.gridId = params.gridId
    this.pathTemplates = params.pathTemplates
  },

  /*
  *При вызове BX.Aclips.BaseGrid.List.openProfile(1) будет открыт профиль пользователя с помощью штатной библиотеки BX.SidePanel в слайдере
  * https://dev.1c-bitrix.ru/api_help/js_lib/sidepanel/sidepanel_instance.php
  * */
  openProfile: function(userId) {
    let profileUrl = this.pathTemplates.userProfile.replace('#ID#', userId)
    BX.SidePanel.Instance.open(profileUrl);
  },

  /*
  * Удаление пользователя будет состоять из нескольких действия:
  *-Окно подтверждения удаления (showRemoveUserConfirmation)
  *-Ajax запрос на удаление пользователя (removeUser)
  *-Перезагрузка грида (reloadGrid)
  * */

  createElement: function() {
    return;
  },
  reloadComponentGrid: function(){
    // Используем свойство объекта this.gridId
    return
  },

  showRemoveUserConfirmation: function (userId) {
    let self = this

    BX.PopupWindowManager.create("popup-remove-user-confirmation", null, {
      titleBar: 'Удаление пользователя',
      content: 'Вы точно хотите удалить пользователя?',
      autoHide: true,
      overlay: {
        backgroundColor: 'black',
        opacity: 500
      },
      buttons: [
        new BX.PopupWindowButton({
          text: 'Да',
          id: 'accept-btn',
          className: 'ui-btn ui-btn-success',
          events: {
            click: function () {
              let callback = () => {
                self.reloadGrid()
                this.popupWindow.close()
              }

              self.removeUser(userId, callback)
            }
          }
        }),

        /*
        * https://dev.1c-bitrix.ru/api_help/js_lib/popup/popupwindowmanager.php
        * */
        new BX.PopupWindowButton({
          text: 'Отмена',
          id: 'decline-btn',
          className: 'ui-btn ui-btn-danger',
          events: {
            click: function () {
              this.popupWindow.close()
            }
          }
        })
      ],
      events: {
        onPopupClose: function () {
          this.destroy()
        }
      }
    }).show()
  },
  removeUser: function (userId, callback) {

    // Ajax запрос на удаление пользователя

    if (callback && {}.toString.call(callback) === '[object Function]') {
      callback()
    }
  },
  reloadGrid() {
    let grid = BX.Main.gridManager.getById(this.gridId);

    if (grid) {
      grid.instance.reload()
    }
  }

}