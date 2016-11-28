let _$location = new WeakMap()
let _$route = new WeakMap()

export default class Diff {
  constructor(wiki, $location, $route) {
    this.wiki = wiki
    _$location.set(this, $location)
    _$route.set(this, $route)

    this.init()
  }

  init() {
    let oldId = _$route.get(this).current.params.oldId
    let newId = _$route.get(this).current.params.newId
    let sectionId = _$route.get(this).current.params.sectionId

    this.wiki.setDisplayedSection(sectionId).then(
      () => {
        this.wiki.setDiffContributions(sectionId, oldId, newId)
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

Diff.$inject = [
  'WikiService',
  '$location',
  '$route'
]
