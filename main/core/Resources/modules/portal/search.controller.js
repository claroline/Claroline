/**
 * Created by panos on 5/30/16.
 */
let _portalService = new WeakMap()
let _$location = new WeakMap()
let _$route = new WeakMap()
let _$rootScope = new WeakMap()
export default class Search {
  constructor(portalService, $routeParams, $location, $route, $rootScope) {
    this.resourceTypes = angular.merge({}, portalService.resourceTypes)
    this.isPortalActive = portalService.isPortalActive();
    this.resourceType = $routeParams.resourceType||'all'
    this.currentPage = $routeParams.page || 1
    this.fileTypes = ['image', 'video', 'document']
    this.fileType = null
    this.query = $routeParams.query||''
    this.portalSearchOptions = {'type': this.resourceType, 'query': this.query, 'onSearchClick': this.onSearchClicked.bind(this)}
    _portalService.set(this, portalService)
    _$location.set(this, $location)
    _$route.set(this, $route)
    _$rootScope.set(this, $rootScope)
    this.init()
  }

  init() {
    if (Object.getOwnPropertyNames(this.resourceTypes).length > 0) {
      this._fixResourceTypeOrder()
      this._fixFileType()
      this.search()
    }
  }

  search() {
    _portalService.get(this).search(this.fileType || this.resourceType, this.query, this.currentPage).then(
      data => {
        this.pagination = data
        this.currentPage = this.pagination.currentPage
        this.resources = data.data
      }
    )
  }

  pageChanged() {
    this.search()
    this._locationSearchNoReload({"query": this.query, "page": this.currentPage})
    this.currentPage = this.pagination.currentPage
  }

  onSearchClicked(query) {
    if (query != this.query) {
      this.query = query
      this.currentPage = 1
      this.search()
      this._locationSearchNoReload({"query": query, "page": this.currentPage})
      this.currentPage = this.pagination.currentPage
    }
  }

  _fixFileType() {
    let idx = this.fileTypes.indexOf(this.resourceType)
    if (idx > -1) {
      this.fileType = this.resourceType
      this.resourceType = 'file'
    }
  }

  _fixResourceTypeOrder() {
    let idx = this.resourceTypes.more.indexOf(this.resourceType)
    if (idx > -1) {
      let tmp = this.resourceTypes.visible.pop()
      this.resourceTypes.visible.push(this.resourceType)
      this.resourceTypes.more.splice(idx, 1)
      this.resourceTypes.more.unshift(tmp)
    }
  }

  _locationSearchNoReload(parameters) {
    let $location = _$location.get(this)
    let $route = _$route.get(this)
    let $rootScope = _$rootScope.get(this)
    let oldRoute = $route.current;
    let unsubscribe = $rootScope.$on('$locationChangeSuccess', function () {
      if (oldRoute) {
        $route.current = oldRoute;
        oldRoute = null;
      }
      unsubscribe();
      unsubscribe = null;
    })
    $location.search(parameters)
  }
}
Search.$inject = ['portalService', '$routeParams', '$location', '$route', '$rootScope']