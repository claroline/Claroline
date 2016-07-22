/**
 * Created by panos on 5/30/16.
 */
let _portalService = new WeakMap()
let _$location = new WeakMap()
export default class Index {
  constructor(portalService, $location) {
    this.isPortalActive = portalService.isPortalActive()
    this.portalSearchOptions = {'type': 'all', 'query': '', 'onSearchClick': this.onSearchClicked.bind(this)}
    _portalService.set(this, portalService)
    _$location.set(this, $location)
    this.init()
  }

  init() {
    if (this.isPortalActive) {
      _portalService.get(this).index().then(
        data => {
          this.images = data.images
          this.lastResources = data.lastResources
        }
      )
    }
  }

  onSearchClicked(query) {
    _$location.get(this).path("search/all").search("query", query)
  }
}
Index.$inject = ['portalService', '$location']