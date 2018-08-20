export default class ExternalSourceListController {
  constructor(sourceListService) {
    this._externalSourceListService = sourceListService
    this.externalSources = []
    this.alerts = []
    this._init()
  }

  removeSource(source) {
    this._externalSourceListService.remove(source).then( sources => {
      this.externalSources = sources
      this.alerts.push({'type' : 'success', 'msg' : 'source_delete_success'})
    }, () => {
      this.alerts.push({'type' : 'danger', 'msg' : 'source_delete_error'})
    })
  }

  closeAlert(index) {
    this.alerts.splice(index, 1)
  }

  _init() {
    this._externalSourceListService.getExternalSources().then(externalSources => {
      this.externalSources = externalSources
    }, () => {
      this.externalSources = []
    })
  }
}
ExternalSourceListController.$inject = ['externalSourceListService']