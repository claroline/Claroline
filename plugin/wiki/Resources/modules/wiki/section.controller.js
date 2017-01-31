let _$scope = new WeakMap()
let _$resource = new WeakMap()
let _$location = new WeakMap()
let _$route = new WeakMap()
let _transFilter = new WeakMap()
let _Messages= new WeakMap()

export default class SectionController {
  constructor($scope, $resource, $location, $route, wiki, transFilter, Messages) {
    _$scope.set(this, $scope)
    _$resource.set(this, $resource)
    _$location.set(this, $location)
    _$route.set(this, $route)
    _transFilter.set(this, transFilter)
    _Messages.set(this, Messages)

    this.wiki = wiki
    this.parent = null

    // Default checked checkboxes
    this.oldidCheckedIndex = 1
    this.diffCheckedIndex = 0

    this.init()
  }

  init() {
    let sectionId = _$route.get(this).current.pathParams.sectionId
    this.wiki.setDisplayedSection(sectionId)

    // Check first and second radiobuttons by default and update model
    _$scope.get(this).$on('ngRepeatLoop', (event, index, element) => {
      if (index === 0) {
        this.diff = element.id
      }
      if (index === 1) {
        this.oldid = element.id
      }
    })
  }

  displayHome() {
    _$location.get(this).url('/')
  }

  displayContribution(section, contribution) {
    this.wiki.displayedContribution = contribution
    let url = `/section/${section.id}/contribution/${contribution.id}`
    _$location.get(this).url(url)
  }

  setActiveContribution(section, contribution) {
    this.wiki.defineAsActive(section, contribution).then(
      () => {},
      () => {
        this._setMessage('success', 'icap_wiki_set_active_contribution_error')
      }
    )
  }

  displayDiff(section, form) {
    _$location.get(this).url(`/section/${section.id}/compare/${form.oldid.$modelValue}/${form.diff.$modelValue}`)
  }

  _setMessage(type, msg) {
    _Messages.get(this).push({
      type: type,
      msg: _transFilter.get(this)(msg, {}, 'icap_wiki')
    })
  }

}

SectionController.$inject = [
  '$scope',
  '$resource',
  '$location',
  '$route',
  'WikiService',
  'transFilter',
  'Messages'
]
