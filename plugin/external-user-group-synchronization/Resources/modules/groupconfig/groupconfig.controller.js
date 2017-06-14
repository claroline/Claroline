let _url = new WeakMap()
let _transFilter = new WeakMap()

export default class ExternalSourceGroupConfigController {
  constructor(sourceGroupConfigService, externalSource, sourceConfig, tableNames, groupFieldNames, userGroupFieldNames, url, $window, transFilter) {
    this._externalSourceGroupConfigService = sourceGroupConfigService
    this.externalSource = externalSource
    this.sourceConfig = sourceConfig
    this.tableNames = tableNames
    this.groupFieldNames = groupFieldNames
    this.userGroupFieldNames = userGroupFieldNames
    _url.set(this, url)
    this._$window = $window
    _transFilter.set(this, transFilter)
    this.alerts = []
    this.is_request_pending = false

    this.init()
  }

  init() {
    // Try to detect if an error has been made using the simple form configuration
    if (
      (this.tableNames.length
      && 'group_config' in this.sourceConfig
      && 'table' in this.sourceConfig.group_config
      && !this.tableNames.includes(this.sourceConfig.group_config.table))
      ||
      (this.tableNames.length
      && 'group_config' in this.sourceConfig
      && 'table' in this.sourceConfig.group_config
      && 'user_group_config' in this.sourceConfig.group_config
      && 'table' in this.sourceConfig.group_config.user_group_config
      && !this.tableNames.includes(this.sourceConfig.group_config.user_group_config.table))
    ) {
      // Alert and reset data : the user will have to reconfigure the group source
      this._setAlert('danger', 'group_config_update_discrepancy')
      this.sourceConfig.group_config = []
    }
  }

  groupTableChange() {
    this.sourceConfig.group_config.fields = {}
    this.is_request_pending  = true
    this._externalSourceGroupConfigService.getFieldNames(this.externalSource, this.sourceConfig.group_config.table).then(response => {
      this.groupFieldNames = response.data
    }).finally(() => {
      this.is_request_pending = false
    })
  }

  userGroupTableChange() {
    this.sourceConfig.group_config.user_group_config.fields = {}
    this.is_request_pending  = true
    this._externalSourceGroupConfigService.getFieldNames(this.externalSource, this.sourceConfig.group_config.user_group_config.table).then(response => {
      this.userGroupFieldNames = response.data
    }).finally(() => {
      this.is_request_pending = false
    })
  }

  cancel() {
    this._$window.location.href = _url.get(this)('claro_admin_external_sync_config_index')
  }

  save() {
    this.is_request_pending  = true
    this._externalSourceGroupConfigService.save(this.externalSource, this.sourceConfig).then(() => {
      this._$window.location.href = _url.get(this)('claro_admin_external_sync_config_index')
    },
    () => {
      this._setAlert('danger', 'group_config_update_failure')
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
ExternalSourceGroupConfigController.$inject = [
  'externalSourceGroupConfigService',
  'externalSource',
  'sourceConfig',
  'tableNames',
  'groupFieldNames',
  'userGroupFieldNames',
  'url',
  '$window',
  'transFilter'
]