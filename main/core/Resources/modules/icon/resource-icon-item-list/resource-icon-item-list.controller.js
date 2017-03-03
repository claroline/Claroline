export default class ResourceIconItemListController {
  constructor(iconItemService) {
    this._iconItemService = iconItemService
    this.iconSet = null
    this.icons = []
    this.alerts = []
    this._init()
  }

  removeIcon(iconItem) {
    this._iconItemService.remove(iconItem).then( () => {
      this.alerts.push({'type' : 'success', 'msg' : 'icon_set_icon_delete_success'})
    }, () => {
      this.alerts.push({'type' : 'danger', 'msg' : 'icon_set_icon_delete_error'})
    })
  }

  updateIcon(iconItem, newIconFile) {
    this._iconItemService.uploadNewIcon(iconItem, newIconFile).then( data  => {
      if (data !== null) {
        this.alerts.push({'type' : 'success', 'msg' : 'icon_set_icon_update_success'})
      }
    }, () => {
      this.alerts.push({'type' : 'danger', 'msg' : 'icon_set_icon_update_error'})
    })
  }

  closeAlert(index) {
    this.alerts.splice(index, 1)
  }

  _init() {
    this._iconItemService.getIcons().then(icons => {
      this.icons = icons
    }, () => {
      this.icons = []
    })

    this._iconItemService.getIconSet().then(iconSet => {
      this.iconSet = iconSet
    }, () => {
      this.iconSet = null
    })
  }
}
ResourceIconItemListController.$inject = ['resourceIconItemService']