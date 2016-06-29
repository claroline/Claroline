/**
 * Created by panos on 5/30/16.
 */
let _$http = new WeakMap()
let _$q = new WeakMap()
let _$filter = new WeakMap()
export default class PortalService {
  constructor(resourceTypes, $http, $q, $filter) {
    _$http.set(this, $http)
    _$q.set(this, $q)
    _$filter.set(this, $filter)
    this.resourceTypes = resourceTypes
    this.indexData = null
    this.searchResults = {}
  }

  index() {
    if (this.indexData == null || this.indexData.length == 0) {
      let path = this._path('claro_portal_api_get')
      let q = this._request('get', path).then(
        data => {
          this.indexData = data
          return this._resolve(data)
        },
        data => {
          return this._reject(data)
        }
      )

      return q
    } else {
      return this._resolve(this.indexData);
    }
  }

  search(resourceType, query, page) {
    resourceType = resourceType || 'all'
    query = query || ''
    page = page || 1
    let path = this._path('claro_portal_api_search', {'resourceType': resourceType, 'query': query, 'page': page})
    let q = this._request('get', path).then(
      data => {
        return this._resolve(data)
      },
      data => {
        return this._reject(data)
      }
    )

    return q
  }

  isPortalActive() {
    return Object.keys(this.resourceTypes).length > 0
  }

  _path (name, params = {}) {
    return _$filter.get(this)('path')(name, params)
  }

  _request (method, path, data = null, config = {}) {
    return _$http.get(this)({method: method, url: path, data: data, config: config}).then(
      response => {
        if (typeof response.data === 'object') {
          return this._resolve(response.data)
        } else {
          return this._reject(response.data)
        }
      }, response => {
        return this._reject(response.data)
      }
    )
  }

  _reject (data) {
    return _$q.get(this).reject(data)
  }

  _resolve (data) {
    return _$q.get(this).resolve(data)
  }
}
PortalService.$inject = ['portal.types', '$http', '$q', '$filter']