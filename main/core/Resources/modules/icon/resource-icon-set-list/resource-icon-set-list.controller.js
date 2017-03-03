export default class ResourceIconSetListController {
  constructor(iconSetService) {
    this._iconSetService = iconSetService
    this.iconSets = []
    this.alerts = []
    this._init()
  }

  removeIconSet(iconSet) {
    this._iconSetService.remove(iconSet).then( iconSets => {
      this.iconSets = iconSets
      this.alerts.push({'type': 'success', 'msg': 'icon_set_delete_success'})
    }, () => {
      this.alerts.push({'type': 'danger', 'msg': 'icon_set_delete_error'})
    })
  }

  closeAlert(index) {
    this.alerts.splice(index, 1)
  }

  _init() {
    this._iconSetService.getList().then(iconSets => {
      this.iconSets = iconSets
    }, () => {
      this.iconSets = []
    })
  }
}
ResourceIconSetListController.$inject = ['resourceIconSetService']