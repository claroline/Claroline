import angular from 'angular/index'

let _wiki = new WeakMap()
let _$resource = new WeakMap()
let _$q = new WeakMap()
let _url = new WeakMap()
let _transFilter = new WeakMap()

export default class WikiService {
  constructor($resource, wiki, $q, url, transFilter) {
    _wiki.set(this, wiki)
    _$resource.set(this, $resource)
    _$q.set(this, $q)
    _url.set(this, url)
    _transFilter.set(this, transFilter)

    this.displayedSection = null
    this.diff = null
    this.oldWiki = angular.copy(_wiki.get(this))
  }

  get activeUserId() { return _wiki.get(this).activeUserId }
  get id() { return _wiki.get(this).id }
  get title() { return _wiki.get(this).title }
  get mode() { return _wiki.get(this).mode }
  set mode(mode) { _wiki.get(this).mode = mode }
  get displaySectionNumbers() { return _wiki.get(this).displaySectionNumbers }
  set displaySectionNumbers(displaySectionNumbers) { _wiki.get(this).displaySectionNumbers = displaySectionNumbers }
  get isLoggedIn() { return _wiki.get(this).isLoggedIn }
  get isAdmin() { return _wiki.get(this).isAdmin }
  get isPDFExportActive() { return _wiki.get(this).isPDFExportActive }
  get sections() { return _wiki.get(this).sections }
  set sections(sections) { _wiki.get(this).sections = sections }
  get deletedSections() { return _wiki.get(this).deletedSections }
  set deletedSections(deletedSections) { _wiki.get(this).deletedSections = deletedSections }
  get notificationButton() { return _wiki.get(this).notificationButton }

  setDisplayedSection(id) {
    if (!this.displayedSection) {
      this.displayedSection = this._recursiveSearch(this.sections, 'id', id)
    }

    // Do we need to load contributions from API ?
    // (only if section was found => the requested section isn't a soft deleted one)
    if (this.displayedSection && !('contributions' in this.displayedSection)) {
      return this.loadContributions(this.displayedSection)
    }

    return _$q.get(this).resolve()
  }

  setDisplayedContribution(id) {
    if (!this.displayedContribution && this.displayedSection) {
      this.displayedContribution = this._recursiveSearch(this.displayedSection.contributions, 'id', id)
    }
  }

  loadContributions(section) {
    const url = _url.get(this)('icap_wiki_api_get_wiki_section_contribution', {
      'wiki': this.id,
      'section': section.id
    })

    let Contribution = _$resource.get(this)(url)
    let contribution = Contribution.get(() => {
      section.contributions = contribution.response
    })

    return contribution.$promise
  }

  setDiffContributions(sectionId, oldId, newId) {
    const url = _url.get(this)('icap_wiki_api_get_wiki_section_contribution_diff', {
      'wiki': this.id,
      'section': sectionId,
      'oldContributionId': oldId,
      'newContributionId': newId
    })
    let Diff = _$resource.get(this)(url)
    let diff = Diff.get(() => {
      this.diff = diff.response
    })
  }

  _recursiveSearch(haystack, key, value, withParent = false , parent = {}) {
    for (let element of haystack) {
      if (element[key] === value) {
        if (withParent) {
          element.parent = parent
        }
        return element
      }
      if ('__children' in element) {
        let found = this._recursiveSearch(element.__children, key, value, withParent, element)
        if (found) return found
      }
    }
    return null
  }

  addSection(parent) {
    let newSection = this.newEmptySection
    newSection.parentId = parent.id
    newSection.level = parent.level + 1
    parent.__children.push(newSection)
  }

  editSection(section, newContrib, updatedSection) {
    // If section doesn't exists in the database, the server will create a new one !

    const url = _url.get(this)('icap_wiki_api_post_wiki_section_contribution', {
      'wiki': this.id,
      'section': section.id,
      'visible': updatedSection.visible,
      'referenceSectionId': updatedSection.referenceSectionId,
      'isBrother': updatedSection.isBrother
    })
    
    let Contribution = _$resource.get(this)(url)
    let contribution = new Contribution(newContrib)
    contribution.contributor = this.activeUserId
    contribution.parentSectionId = section.parentId
    if (!('title' in contribution)) {
      contribution.title = this.title
    }

    return contribution.$save(
      success => {
        this.sections = success.sections
      }
    )
  }

  updateSection(sect, updatedSec) {
    const url = _url.get(this)('icap_wiki_api_put_wiki_section', {
      'wiki': this.id,
      'section': sect.id,
      'visible': updatedSec.visible,
      'referenceSectionId': updatedSec.referenceSectionId,
      'isBrother': !!updatedSec.isBrother

    })
    let Section = _$resource.get(this)(url, null,
      {
        'update': { method: 'PUT'}
      })

    let section = new Section(updatedSec)

    return section.$update(
      success => {
        this.sections = success.sections
      }
    )
  }

  defineAsActive(section, contribution) {
    const url = _url.get(this)('icap_wiki_api_patch_wiki_section_contribution', {
      'wiki': this.id,
      'section': section.id,
      'contribution': contribution.id
    })
    let Contribution = _$resource.get(this)(url, null,
      {
        'setActive': { method: 'PATCH'}
      })
    let contrib = new Contribution(contribution)
    return contrib.$setActive(
      () => {
        // Find old active contribution and mark it as pre-active
        contribution.is_active = true
        section.activeContribution = contribution
      }
    )
  }

  toggleVisibility(sect) {
    const url = _url.get(this)('icap_wiki_api_put_wiki_section', {
      'wiki': this.id,
      'section': sect.id,
      'visible': !sect.visible
    })
    
    let Section = _$resource.get(this)(url, null,
      {
        'update': { method: 'PUT'}
      })

    let section = new Section(sect)

    return section.$update(
      () => {
        sect.visible = !sect.visible
      }
    )
  }

  updateOptions() {
    // Save old options
    this.oldWiki = angular.copy(_wiki.get(this))

    const url = _url.get(this)('icap_wiki_api_patch_wiki', {
      'wiki': this.id
    })
    let Wiki = _$resource.get(this)(url, null,
      {
        'updateOptions': { method: 'PATCH'}
      })

    let wiki = new Wiki(_wiki.get(this))
    return wiki.$updateOptions(
      () => {},
      () => {
        // revert wiki mode
        this.revertOptions()
      }
    )
  }

  revertOptions() {
    // This function is also called by wiki controller
    this.wiki = this.oldWiki
  }

  softDeleteSection(sect, withChildren) {
    const url = _url.get(this)('icap_wiki_api_delete_wiki_section', {
      'wiki': this.id,
      'section': sect.id
    })
    let Section = _$resource.get(this)(url, { withChildren: withChildren })
    let section = new Section(sect)

    return section.$delete(
      success => {
        // Get updated list of sections and soft deleted sections from server response
        this.sections = success.sections
        this.deletedSections = success.deletedSections
      }
    )
  }

  hardDeleteSection(sect, idx) {
    const url = _url.get(this)('icap_wiki_api_delete_wiki_section', {
      'wiki': this.id,
      'section': sect.id
    })
    let Section = _$resource.get(this)(url)
    let section = new Section(sect)

    return section.$delete(
      () => {
        this.deletedSections.splice(idx, 1)
      }
    )
  }

  restoreSection(sect) {
    const url = _url.get(this)('icap_wiki_api_patch_wiki_section', {
      'wiki': this.id,
      'section': sect.id
    })
    let Section = _$resource.get(this)(url, null,
      {
        'restore': { method: 'PATCH' }
      })

    let section = new Section(sect)
    return section.$restore(
      () => {
        // Strip section out of the soft deleted ones
        let restoredSectionIdx = this.deletedSections.indexOf(sect)
        let restoredSection = this.deletedSections.splice(restoredSectionIdx, 1)[0]
        restoredSection.deleted = false
        restoredSection.deletionDate = null

        // Place section at the end of the wiki
        this.sections[0].__children.push(restoredSection)
      }
    )
  }

  get newEmptySection() {
    return {
      'isNew': true,
      'id': 0,
      'visible': true,
      'creationDate': '',
      'deleted': false,
      'root': 1,
      'activeContribution': {
        'id': 0,
        'title': _transFilter.get(this)('new_section', {}, 'icap_wiki'),
        'text': '',
        'creationDate': ''
      },
      '__children': []
    }
  }
}

WikiService.$inject = [
  '$resource',
  'wiki',
  '$q',
  'url',
  'transFilter'
]
