import angular from 'angular/index'
import confirmDeletionTemplate from './confirmDeletion.partial.html'
import confirmHardDeletionTemplate from './confirmHardDeletion.partial.html'

let _$resource = new WeakMap()
let _$location = new WeakMap()
let _$anchorScroll = new WeakMap()
let _Messages = new WeakMap()
let _transFilter = new WeakMap()
let _modalInstance = new WeakMap()
let _modalFactory = new WeakMap()
let _$scope = new WeakMap()
let _url = new WeakMap()

export default class WikiController {

  constructor($resource, wikiService, $location, $anchorScroll, $routeParams, Messages, transFilter, modal, $scope, tinyMceConfig, url) {
    _$resource.set(this, $resource)
    _$location.set(this, $location)
    _$anchorScroll.set(this, $anchorScroll)
    _Messages.set(this, Messages)
    _transFilter.set(this, transFilter)
    _modalInstance.set(this, null)
    _modalFactory.set(this, modal)
    _$scope.set(this, $scope)
    _url.set(this, url)

    this.wiki = wikiService
    this.$routeParams = $routeParams
    this.tinymceOptions = tinyMceConfig
    this.currentContributions = []
    this.currentSections = []
    this.disableFormButtons = false
    this.disableModalButtons = false
    this.isFormOpen = false
  }
  
  backToTop() {
    _$location.get(this).hash('top')
    _$anchorScroll.get(this)()
  }

  getFontSize(level) {
    return level < 8 ? 21 - level + 'px' : '14px'
  }

  get moderateModeWarningText() {
    return _transFilter.get(this)('moderate_mode_warning', {}, 'icap_wiki')
  }

  getDeletionDate(myDate) {
    return myDate.date || myDate
  }

  displayUrl(url) {
    _$location.get(this).url(url)
  }

  get pdfExportUrl() {
    return _url.get(this)('icap_wiki_view', {
      'wikiId': this.wiki.id,
      '_format': 'pdf'
    })
  }

  displayOptions() {
    this.wiki.oldMode = this.wiki.mode
    _$location.get(this).url('/options')
  }

  displayHome() {
    _$location.get(this).url('/')
  }

  displaySection(section) {
    this.wiki.displayedSection = section
    _$location.get(this).url(`/section/${section.id}`)
  }

  displayContribution(section, contribution) {
    this.wiki.displayedContribution = contribution
    let url = `/section/${section.id}/contribution/${contribution.id}`
    _$location.get(this).url(url)
  }

  addSection(section) {
    this.isFormOpen = true
    this.wiki.addSection(section)
    this.editSection(section.__children[section.__children.length - 1])
  }

  editSection(section) {
    this.isFormOpen = true

    // Cancel every other current contribution (allow display of only one edit form at a time)
    for (let k in this.currentContributions) {
      if (k != section.id) {
        delete this.currentContributions[k]
      }
    }

    // Make edit form appear
    this.currentSections[section.id] = angular.copy(section)

    // Remove children of the duplicated section
    delete this.currentSections[section.id].__children

    // Set the reference section Id key
    this.currentSections[section.id].referenceSectionId = null

    this.currentContributions[section.id] = !this.currentContributions[section.id] ?
      angular.copy(section.activeContribution) :
      null

    // Pre-fetch history for this section
    if ((!('contributions' in section) || section.contributions.length === 0) && section.id !== 0) {
      this.wiki.loadContributions(section)
    }
  }

  cancelSection(section) {
    // Do not show this section anymore if creation has been canceled before being saved on server side
    if (section.isNew) {
      section.isStale = true
    }

    // Remove contribution from current contribution array
    delete this.currentContributions[section.id]

    // Re-enable all the buttons
    this.isFormOpen = false
  }

  cancelNewSection() {
    this.createRootSection = false
  }

  saveSection(section) {
    this.disableFormButtons = true

    // Has the title or text been modified ? If not, just toggle visibility or move section
    if (this.currentContributions[section.id].title !== section.activeContribution.title || this.currentContributions[section.id].text !== section.activeContribution.text) {
      // Edit section with a new contribution
      this._saveSectionWithNewContribution(section, this.currentContributions[section.id], this.currentSections[section.id])
    } else {
      // Update section
      this._saveSectionWithoutNewContribution(section, this.currentSections[section.id])
    }
  }

  toggleVisibility(section) {
    this.isToggling = true
    this.wiki.toggleVisibility(section)
      .finally(() => this.isToggling = false)
  }

  saveNewSection(section) {
    this.disableFormButtons = true
    this._saveSectionWithNewContribution(section, this.currentContributions[section.id], this.currentSections[section.id])
  }

  confirmSoftDeleteSection(section) {
    this.sectionToDelete = section
    this._modal(confirmDeletionTemplate)
  }

  softDeleteSection(section, form) {
    this.disableModalButtons = true

    // Check if the checkbox exists and is checked
    let withChildren = !!(form.deleteChildren && form.deleteChildren.$modelValue)

    this.wiki.softDeleteSection(section, withChildren).then(
      () => {
        this.currentContributions = []
        this.currentSections = []
        this._setMessage('success', 'icap_wiki_section_delete_success')
      },
      () => {
        this._setMessage('danger', 'icap_wiki_section_delete_error')
      }
    ).finally(
      () => {
        this.isFormOpen = false
        this.disableModalButtons = false
        this._cancelModal()
      }

    )
    this.currentContributions[section.id] = null
    this.currentSections[section.id] = null
  }

  confirmHardDeleteSection(section, idx) {
    this.sectionToHardDelete = section
    this.sectionToHardDelete.idx = idx
    this._modal(confirmHardDeletionTemplate)
  }

  hardDeleteSection(section) {
    this.disableModalButtons = true

    this.wiki.hardDeleteSection(section, this.sectionToHardDelete.idx).then(
      () => {
        this._setMessage('success', 'icap_wiki_section_remove_success')
      },
      () => {
        this._setMessage('danger', 'icap_wiki_section_remove_error')
      }
    ).finally(() => {
      // Cancel modal
      this.disableModalButtons = false
      this._cancelModal()
    })
  }

  cancelEditOptions() {
    this.wiki.revertOptions()
    _$location.get(this).url('/')
  }

  saveOptions() {
    this.disableFormButtons = true

    this.wiki.updateOptions().then(
      () => {
        _$location.get(this).url('/')
        this._setMessage('success', 'icap_wiki_options_save_success')
      },
      () => {
        this._setMessage('danger', 'icap_wiki_options_save_error')
      }
    ).finally(() => {
      this.disableFormButtons = false
    })
  }

  restoreSection(section) {
    this.disableFormButtons = true

    this.wiki.restoreSection(section).then(
      () => {
        this._setMessage('success', 'icap_wiki_section_restore_success')
      },
      () => {
        this._setMessage('danger', 'icap_wiki_section_restore_error')
      }
    ).finally(() => {
      this.disableFormButtons = false
    })
  }

  _saveSectionWithNewContribution(section, newContrib, updatedSection) {
    this.wiki.editSection(section, newContrib, updatedSection).then(
      success => {
        if (newContrib.id === 0) {
          this._setMessage('success', 'icap_wiki_section_add_success')
        } else {
          this._setMessage('success', 'icap_wiki_section_update_success')
        }
        _$location.get(this).hash(`sect-${success.section.id}`)
      },
      () => {
        if (newContrib.id === 0) {
          this._setMessage('danger', 'icap_wiki_section_add_error')
        } else {
          this._setMessage('danger', 'icap_wiki_section_update_error')
        }
        _$location.get(this).hash('top')
      }
    ).finally(
      () => {
        this.currentContributions = []
        this.currentSections = []
        this.disableFormButtons = false
        this.isFormOpen = false

        _$anchorScroll.get(this)()
      }
    )
  }

  _saveSectionWithoutNewContribution(section, updatedSection) {
    this.wiki.updateSection(section, updatedSection).then(
      () => {
        this.currentContributions[section.id] = null
        this.currentSections[section.id] = null
        this._setMessage('success', 'icap_wiki_section_update_success')
      },
      () => {
        this._setMessage('danger', 'icap_wiki_section_update_error')
      }
    ).finally(() => {
      this.disableFormButtons = false
      this.isFormOpen = false

      _$location.get(this).hash(`sect-${section.id}`)
      _$anchorScroll.get(this)()
    })
  }

  _setMessage(type, msg) {
    _Messages.get(this).push({
      type: type,
      msg: _transFilter.get(this)(msg, {}, 'icap_wiki')
    })
  }

  _modal(template) {
    _modalInstance.set(this, _modalFactory.get(this).open(template, _$scope.get(this)))
  }

  _cancelModal() {
    _modalInstance.get(this).dismiss()
  }

}

WikiController.$inject = [
  '$resource',
  'WikiService',
  '$location',
  '$anchorScroll',
  '$routeParams',
  'Messages',
  'transFilter',
  'wikiModal',
  '$scope',
  'tinyMceConfig',
  'url'
]
