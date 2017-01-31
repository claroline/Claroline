let _$location = new WeakMap()
let _$route = new WeakMap()

export default class ContributionController {
  constructor($location, $route, wiki) {
    _$location.set(this, $location)
    _$route.set(this, $route)

    this.wiki = wiki

    this.init()
  }

  init() {
    let sectionId = _$route.get(this).current.pathParams.sectionId
    this.wiki.setDisplayedSection(sectionId).then(
      () => {
        let contributionId = _$route.get(this).current.pathParams.contributionId
        this.wiki.setDisplayedContribution(contributionId)
      }
    )
  }

  displayHome() {
    _$location.get(this).url('/')
  }

  displaySection(section) {
    _$location.get(this).url(`/section/${section.id}`)
  }

}

ContributionController.$inject = [
  '$location',
  '$route',
  'WikiService'
]
