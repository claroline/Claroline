/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Translator*/

export default class MenuCtrl {
  constructor($state, ClacoFormService, EntryService) {
    this.$state = $state
    this.ClacoFormService = ClacoFormService
    this.EntryService = EntryService
    this.config = ClacoFormService.getResourceDetails()
    this.showNbEntries = this.config['display_nb_entries'] === 'all' || this.config['display_nb_entries'] === 'published'
  }

  canEdit() {
    return this.ClacoFormService.getCanEdit()
  }

  getResourceNodeName() {
    return this.ClacoFormService.getResourceNodeName()
  }

  getSuccessMessage() {
    return this.ClacoFormService.getSuccessMessage()
  }

  getErrorMessage() {
    return this.ClacoFormService.getErrorMessage()
  }

  clearSuccessMessage() {
    this.ClacoFormService.clearSuccessMessage()
  }

  clearErrorMessage() {
    this.ClacoFormService.clearErrorMessage()
  }

  canAdd() {
    return this.ClacoFormService.getCanCreateEntry()
  }

  canSearch() {
    return this.ClacoFormService.getCanSearchEntry()
  }

  getNbEntries() {
    return this.EntryService.getNbEntries()
  }

  getNbPublishedEntries() {
    return this.EntryService.getNbPublishedEntries()
  }

  getRandomEntry() {
    this.ClacoFormService.getRandomEntryId(this.ClacoFormService.getResourceId()).then(d => {
      if (d) {
        if ((typeof d === 'number') && (d > 0)) {
          this.$state.go('entry_view', {entryId: d})
        } else {
          this.ClacoFormService.setErrorMessage(Translator.trans('no_available_random_entry', {}, 'clacoform'))
        }
      }
    })
  }
}