import _ from 'lodash'

export default class ResourceIconSetService {
  constructor(iconSets, $http, $q, $filter) {
    this._http = $http
    this._q = $q
    this._filter = $filter
    this.iconSets = iconSets
  }

  getList() {
    return this._resolve(this.iconSets)
  }

  remove(iconSet) {
    return this._request('DELETE', this._path('claro_admin_icon_set_delete', {'id': iconSet.id})).then(
      () => {
        _.remove(this.iconSets, st => st.id == iconSet.id)
        return this._resolve(this.iconSets)
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
ResourceIconSetService.$inject = ['iconSets', '$http', '$q', '$filter']