let _url = new WeakMap()
let _transFilter = new WeakMap()

export default class ExternalSourceUserConfigController {
  constructor(sourceUserConfigService, externalSource, sourceConfig, tableNames, fieldNames, url, $window, transFilter) {
    this._externalSourceUserConfigService = sourceUserConfigService
    this.externalSource = externalSource
    this.sourceConfig = sourceConfig
    this.tableNames = tableNames
    this.tableFieldNames = fieldNames
    _url.set(this, url)
    this._$window = $window
    _transFilter.set(this, transFilter)
    this.alerts = []
    this.is_request_pending = false

    this.init()
  }

  init() {
    // Try to detect if an error has been made using the simple form configuration
    if (this.tableNames.length
      && 'user_config' in this.sourceConfig
      && 'table' in this.sourceConfig.user_config
      && !this.tableNames.includes(this.sourceConfig.user_config.table)
    ) {
      // Alert and reset data : the user will have to reconfigure the user source
      this._setAlert('danger', 'user_config_update_discrepancy')
      this.sourceConfig.user_config = []
    }
  }

  tableChange() {
    this.sourceConfig.user_config.fields = {}
    this.is_request_pending  = true
    this._externalSourceUserConfigService.getFieldNames(this.externalSource ,this.sourceConfig.user_config.table).then(response => {
      this.tableFieldNames = response.data
    }).finally(() => {
      this.is_request_pending = false
    })
  }

  cancel() {
    this._$window.location.href = _url.get(this)('claro_admin_external_sync_config_index')
  }

  save() {
    this.is_request_pending  = true
    this._externalSourceUserConfigService.save(this.externalSource, this.sourceConfig).then(() => {
      this._$window.location.href = _url.get(this)('claro_admin_external_sync_config_index')
    },
    () => {
      this._setAlert('danger', 'user_config_update_failure')
      this.is_request_pending = false
    })
  }

  _setAlert(type, msg) {
    this.alerts.push({
      type: type,
      msg: _transFilter.get(this)(msg, {}, 'claro_external_user_group')
    })
  }
}
ExternalSourceUserConfigController.$inject = [
  'externalSourceUserConfigService',
  'externalSource',
  'sourceConfig',
  'tableNames',
  'fieldNames',
  'url',
  '$window',
  'transFilter'
]