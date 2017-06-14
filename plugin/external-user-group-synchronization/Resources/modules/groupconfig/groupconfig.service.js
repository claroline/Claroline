let _url = new WeakMap()

export default class ExternalSourceGroupConfigService {

  constructor(url, $http) {
    _url.set(this, url)
    this._$http = $http
  }

  getFieldNames(source, table) {
    const url = _url.get(this)('claro_admin_external_sync_table_columns', {
      'source': source,
      'table': table
    })

    return this._$http.get(url)
  }

  save(source, sourceConfig) {
    const url = _url.get(this)('claro_admin_external_sync_source_update_group_configuration', {
      'source': source
    })

    let postData = {
      'source': source,
      'data': sourceConfig
    }

    return this._$http.post(url, {'sourceConfig': postData})
  }
}

ExternalSourceGroupConfigService.$inject = ['url', '$http']