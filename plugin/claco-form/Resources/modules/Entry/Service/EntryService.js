/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/
/*global Translator*/
import entrySharesManagementTemplate from '../Partial/entry_shares_management_modal.html'

export default class EntryService {
  constructor($http, $window, $uibModal, ClarolineAPIService, FieldService) {
    this.$http = $http
    this.$window = $window
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
    this.FieldService = FieldService
    this.canEdit = EntryService._getGlobal('canEdit')
    this.resourceId = EntryService._getGlobal('resourceId')
    this.resourceDetails = EntryService._getGlobal('resourceDetails')
    this.entries = []
    this.myEntries = EntryService._getGlobal('myEntries')
    this.managerEntries = EntryService._getGlobal('managerEntries')
    this.nbEntries = EntryService._getGlobal('nbEntries')
    this.nbPublishedEntries = EntryService._getGlobal('nbPublishedEntries')
    this.sharedEntries = EntryService._getGlobal('sharedEntries')
    this.categoryFilter = ''
    this.keywordFilter = ''
    this._updateEntryCallback = this._updateEntryCallback.bind(this)
    this._removeEntryCallback = this._removeEntryCallback.bind(this)
    this._removeAllEntriesCallback = this._removeAllEntriesCallback.bind(this)
    this.initialize()
  }

  _updateEntryCallback(data, statusChanged = false, oldStatus = null) {
    let entry = JSON.parse(data)
    this.formatEntry(entry)
    const entryIndex = this.entries.findIndex(e => e['id'] === entry['id'])
    const myEntryIndex = this.myEntries.findIndex(e => e['id'] === entry['id'])
    const managerEntryIndex = this.managerEntries.findIndex(e => e['id'] === entry['id'])

    if (entryIndex > -1) {
      this.entries[entryIndex] = entry
    }
    if (myEntryIndex > -1) {
      this.myEntries[myEntryIndex] = entry
    }
    if (managerEntryIndex > -1) {
      this.managerEntries[managerEntryIndex] = entry
    }
    if (statusChanged) {
      if (entry['status'] === 1) {
        ++this.nbPublishedEntries
      } else if (oldStatus === 1) {
        --this.nbPublishedEntries
      }
    }
  }

  _removeEntryCallback(data) {
    const entry = JSON.parse(data)
    const entryIndex = this.entries.findIndex(e => e['id'] === entry['id'])
    const myEntryIndex = this.myEntries.findIndex(e => e['id'] === entry['id'])
    const managerEntryIndex = this.managerEntries.findIndex(e => e['id'] === entry['id'])

    if (entryIndex > -1) {
      this.entries.splice(entryIndex, 1)
    }
    if (myEntryIndex > -1) {
      this.myEntries.splice(myEntryIndex, 1)
    }
    if (managerEntryIndex > -1) {
      this.managerEntries.splice(managerEntryIndex, 1)
    }
    --this.nbEntries

    if (entry['status'] === 1) {
      --this.nbPublishedEntries
    }
  }

  _removeAllEntriesCallback() {
    this.entries.splice(0, this.entries.length)
    this.myEntries.splice(0, this.myEntries.length)
    this.managerEntries.splice(0, this.managerEntries.length)
    this.sharedEntries = {}
    this.nbEntries = 0
    this.nbPublishedEntries = 0
  }

  initialize() {
    this.myEntries.forEach(e => this.formatEntry(e))
    this.managerEntries.forEach(e => this.formatEntry(e))
    const url = Routing.generate('claro_claco_form_entries_list', {clacoForm: this.resourceId})
    this.$http.get(url).then(d => {
      const data = JSON.parse(d['data'])
      data.forEach(e => {
        this.formatEntry(e)
        this.entries.push(e)
      })
    })
  }

  getEntries() {
    return this.entries
  }

  getMyEntries() {
    return this.myEntries
  }

  getManagerEntries() {
    return this.managerEntries
  }

  getEntry(entryId) {
    return this.entries.find(e => e['id'] === entryId)
  }

  getEntryById(entryId) {
    const url = Routing.generate('claro_claco_form_entry_retrieve', {entry: entryId})

    return this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        let entry = JSON.parse(d['data'])
        this.formatEntry(entry)

        return entry
      }
    })
  }

  getNbEntries() {
    return this.nbEntries
  }

  getNbPublishedEntries() {
    return this.nbPublishedEntries
  }

  getNbMyEntries() {
    return this.myEntries.length
  }

  getCanOpenEntry(entryId) {
    return this.canEdit ||
      this.isManagerEntry(entryId) ||
      this.isMyEntry(entryId) ||
      (this.resourceDetails['search_enabled'] && (this.getEntryStatus(entryId) === 1))
  }

  getCanEditEntry(entryId) {
    return this.canEdit || this.isManagerEntry(entryId) || (this.resourceDetails['edition_enabled'] && this.isMyEntry(entryId))
  }

  getCanManageEntry(entryId) {
    return this.canEdit || this.isManagerEntry(entryId)
  }

  isMyEntry(entryId) {
    return this.myEntries.find(e => e['id'] === entryId) !== undefined
  }

  isManagerEntry(entryId) {
    return this.managerEntries.find(e => e['id'] === entryId) !== undefined
  }

  getEntryStatus(entryId) {
    const entry = this.entries.find(e => e['id'] === entryId)

    return entry ? entry['status'] : null
  }

  createEntry(resourceId, entryData, entryTitle, keywordsData = []) {
    const url = Routing.generate('claro_claco_form_entry_create', {clacoForm: resourceId})

    return this.$http.post(url, {entryData: entryData, titleData: entryTitle, keywordsData: keywordsData}).then(d => {
      if (d['status'] === 200) {
        let entry = JSON.parse(d['data'])
        this.entries.push(entry)
        this.myEntries.push(entry)
        this.formatEntry(entry)
        ++this.nbEntries

        if (entry['status'] === 1) {
          ++this.nbPublishedEntries
        }

        return entry
      }
    })
  }

  editEntry(entryId, entryData, entryTitle, categoriesData = [], keywordsData = [], callback = null) {
    const url = Routing.generate('claro_claco_form_entry_edit', {entry: entryId})
    const updateCallback = callback !== null ? callback : this._updateEntryCallback

    return this.$http.post(url, {entryData: entryData, titleData: entryTitle, categoriesData: categoriesData, keywordsData: keywordsData}).then(d => {
      if (d['status'] === 200) {
        updateCallback(d['data'])

        return true
      }
    })
  }

  formatEntry(entry) {
    const creationDate = new Date(entry['creationDate'])
    entry['creationDateString'] = `${creationDate.getDate()}/${creationDate.getMonth() + 1}/${creationDate.getFullYear()}`

    if (entry['user']) {
      entry['userString'] = `${entry['user']['firstName']} ${entry['user']['lastName']}`
    } else {
      entry['userString'] = '-'
    }
    if (entry['categories'].length > 0) {
      let categoriesNames = []
      entry['categories'].forEach(c => categoriesNames.push(c['name']))
      entry['categoriesString'] = categoriesNames.join(', ')
    } else {
      entry['categoriesString'] = '-'
    }
    if (entry['keywords'].length > 0) {
      let keywordsNames = []
      entry['keywords'].forEach(k => keywordsNames.push(k['name']))
      entry['keywordsString'] = keywordsNames.join(', ')
    } else {
      entry['keywordsString'] = '-'
    }
    entry['alert'] = (entry['status'] === 0) || entry['comments'].length > 0
    entry['fieldValues'].forEach(v => {
      const fieldId = v['field']['id']
      const fieldLabel = `field_${fieldId}`
      entry[fieldId] = v['fieldFacetValue']['value']

      if (v['fieldFacetValue']['field_facet']['type'] === 3) {
        const valueDate = new Date(v['fieldFacetValue']['value'])
        entry[fieldLabel] = `${valueDate.getDate()}/${valueDate.getMonth() + 1}/${valueDate.getFullYear()}`
      } else if (v['fieldFacetValue']['field_facet']['type'] === 6 || v['fieldFacetValue']['field_facet']['type'] === 10) {
        entry[fieldLabel] = v['fieldFacetValue']['value'] ? v['fieldFacetValue']['value'].join(', ') : ''
      } else if (v['fieldFacetValue']['field_facet']['type'] === 7) {
        entry[fieldLabel] = this.FieldService.getCountryNameFromCode(v['fieldFacetValue']['value'])
      } else {
        entry[fieldLabel] = v['fieldFacetValue']['value']
      }
    })
  }

  deleteEntry(entry, callback = null) {
    const url = Routing.generate('claro_claco_form_entry_delete', {entry: entry['id']})
    const deleteCallback = callback !== null ? callback : this._removeEntryCallback

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      deleteCallback,
      Translator.trans('delete_entry', {}, 'clacoform'),
      Translator.trans('delete_entry_confirm_message', {title: entry['title']}, 'clacoform')
    )
  }

  changeEntryStatus(entry, callback = null) {
    const oldStatus = entry['status']
    const url = Routing.generate('claro_claco_form_entry_status_change', {entry: entry['id']})
    const updateCallback = callback !== null ? callback : this._updateEntryCallback
    this.$http.put(url).then(d => {
      if (d['status'] === 200) {
        updateCallback(d['data'], true, oldStatus)
      }
    })
  }

  getCategoryFilter() {
    return this.categoryFilter
  }

  setCategoryFilter(filter) {
    this.categoryFilter = filter
  }

  getKeywordFilter() {
    return this.keywordFilter
  }

  setKeywordFilter(filter) {
    this.keywordFilter = filter
  }

  getEntryUser(entryId) {
    const url = Routing.generate('claro_claco_form_entry_user_retrieve', {entry: entryId})

    return this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        return JSON.parse(d['data'])
      }
    })
  }

  saveEntryUser(entryId, entryUser) {
    const url = Routing.generate('claro_claco_form_entry_user_save', {entry: entryId})
    this.$http.put(url, {entryUserData: entryUser})
  }

  downloadPdf(entryId) {
    this.$window.location.href = Routing.generate('claro_claco_form_entry_pdf_download', {entry: entryId})
  }

  showEntrySharesManagement(entry) {
    this.$uibModal.open({
      template: entrySharesManagementTemplate,
      controller: 'EntrySharesManagementModalCtrl',
      controllerAs: 'cfc',
      resolve: {
        entry: () => { return entry }
      }
    })
  }

  shareEntry(entryId, usersIds) {
    const url = Routing.generate('claro_claco_form_entry_users_share', {entry: entryId})
    this.$http.put(url, {usersIds: usersIds})
  }

  unshareEntry(entryId, userId) {
    const url = Routing.generate('claro_claco_form_entry_user_unshare', {entry: entryId, user: userId})
    this.$http.delete(url)
  }

  getSharedUsers(entryId) {
    const url = Routing.generate('claro_claco_form_entry_shared_users_list', {entry: entryId})

    return this.$http.get(url)
  }

  isShared(entryId, userId) {
    return this.sharedEntries[entryId] && this.sharedEntries[entryId][userId]
  }

  deleteAllEntries() {
    const url = Routing.generate('claro_claco_form_all_entries_delete', {clacoForm: this.resourceId})

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      this._removeAllEntriesCallback,
      Translator.trans('delete_all_entries', {}, 'clacoform'),
      Translator.trans('delete_all_entries_confirm_msg', {}, 'clacoform')
    )
  }

  static _getGlobal(name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }
}