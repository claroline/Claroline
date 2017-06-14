export default class ExternalSourceListService {
  constructor(externalSources, $http, $q, $filter) {
    this._http = $http
    this._q = $q
    this._filter = $filter
    this.externalSources = externalSources
  }

  getExternalSources() {
    return this._resolve(this.externalSources)
  }

  remove(source) {
    return this._request('DELETE', this._path('claro_admin_external_sync_delete_source', {'source': source})).then(
      data => {
        if (data.deleted === true) {

          let index = this.externalSources.findIndex((elem) => {return elem.slug === source})
          if (index > -1) {
            this.externalSources.splice(index, 1)
          }
        }
        return this._resolve(this.externalSources)
      },
      data => {
        return this._reject(data)
      }
    )
  }

  _path(name, params = {}) {
    return this._filter('path')(name, params)
  }

  _request(method, path, data = null, config = {}) {
    return this._http({method: method, url: path, data: data, config: config}).then(
      response => {
        if (typeof response.data === 'object') {
          return this._resolve(response.data)
        } else {
          return this._reject(response.data)
        }
      },
      response => {
        return this._reject(response.data)
      }
    )
  }

  _reject(data) {
    return this._q.reject(data)
  }

  _resolve(data) {
    return this._q.resolve(data)
  }
}
ExternalSourceListService.$inject = ['externalSources', '$http', '$q', '$filter']