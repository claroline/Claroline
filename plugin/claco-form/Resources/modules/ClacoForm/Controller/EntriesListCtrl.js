/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Translator*/

export default class EntriesManagementCtrl {
  constructor(NgTableParams, ClacoFormService, EntryService, FieldService, CategoryService) {
    this.ClacoFormService = ClacoFormService
    this.EntryService = EntryService
    this.FieldService = FieldService
    this.CategoryService = CategoryService
    this.config = ClacoFormService.getResourceDetails()
    this.fields = FieldService.getFields()
    this.entries = EntryService.getEntries()
    this.myEntries = EntryService.getMyEntries()
    this.managerEntries = EntryService.getManagerEntries()
    this.tableParams = {
      entries: new NgTableParams(
        {count: 20, filter: {categoriesString: EntryService.getCategoryFilter(), keywordsString: EntryService.getKeywordFilter()}},
        {counts: [10, 20, 50, 100], dataset: this.entries}
      ),
      myEntries: new NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.myEntries}
      ),
      managerEntries: new NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.managerEntries}
      )
    }
    this.columns = {entries : {}, myEntries : {}, managerEntries : {}}
    this.columnsKeys = {entries : [], myEntries : [], managerEntries : []}
    this.fieldsColumns = {entries : [], myEntries : [], managerEntries : []}
    this.modes = []
    this.mode = null
    this._updateEntryCallback = this._updateEntryCallback.bind(this)
    this._removeEntryCallback = this._removeEntryCallback.bind(this)
    this.initialize()
  }

  _updateEntryCallback(data, statusChanged = false, oldStatus = null) {
    this.EntryService._updateEntryCallback(data, statusChanged, oldStatus)
    this.tableParams['entries'].reload()
    this.tableParams['myEntries'].reload()
    this.tableParams['managerEntries'].reload()
  }

  _removeEntryCallback(data) {
    this.EntryService._removeEntryCallback(data)
    this.tableParams['entries'].reload()
    this.tableParams['myEntries'].reload()
    this.tableParams['managerEntries'].reload()
  }

  initialize() {
    this.ClacoFormService.clearMessages()
    this.EntryService.setCategoryFilter('')
    this.EntryService.setKeywordFilter('')
    this.initializeModes()
    this.initializeColumns()
  }

  initializeModes() {
    if (this.ClacoFormService.getCanEdit() || this.config['search_enabled']) {
      this.modes.push('all_entries')
    }
    if (this.CategoryService.getIsCategoryManager()) {
      this.modes.push('manager_entries')
    }
    if (!this.ClacoFormService.getIsAnon()) {
      this.modes.push('my_entries')
    }
    if (this.modes.length > 0) {
      this.mode = this.modes[0]
    }
  }

  initializeColumns() {
    const displayMetadata = this.ClacoFormService.getCanEdit() ||
      this.config['display_metadata'] === 'all' ||
      ((this.config['display_metadata'] === 'manager') && this.CategoryService.getIsCategoryManager())
    const transTitle = Translator.trans('title', {}, 'platform')
    const transDate = Translator.trans('date', {}, 'platform')
    const transUser = Translator.trans('user', {}, 'platform')
    const transCategories = Translator.trans('categories', {}, 'platform')
    const transKeywords = Translator.trans('keywords', {}, 'clacoform')
    const transActions = Translator.trans('actions', {}, 'platform')
    const alertFieldColumn = {id: 'alert', sortable: 'alert'}
    const titleFieldColumn = {id: 'title', title: transTitle, filter: {title: 'text'}, sortable: 'title'}
    const dateFieldColumn = {id: 'creationDateString', title: transDate, filter: {creationDateString: 'text'}, sortable: 'creationDate'}
    const userFieldColumn = {id: 'userString', title: transUser, filter: {userString: 'text'}, sortable: 'userString'}

    const titleColumnDisplay = this.checkColumnDefaultValue('title')
    const dateColumnDisplay = this.checkColumnDefaultValue('creationDateString')
    const userColumnDisplay = this.checkColumnDefaultValue('userString')
    const categoriesColumnDisplay = this.checkColumnDefaultValue('categoriesString')
    const keywordsColumnDisplay = this.checkColumnDefaultValue('keywordsString')
    const actionsColumnDisplay = this.checkColumnDefaultValue('actions')

    this.columns['entries']['title'] = {name: transTitle, value: titleColumnDisplay}
    this.columns['myEntries']['title'] = {name: transTitle, value: titleColumnDisplay}
    this.columns['managerEntries']['title'] = {name: transTitle, value: titleColumnDisplay}
    this.columns['managerEntries']['creationDateString'] = {name: transDate, value: dateColumnDisplay}
    this.columns['managerEntries']['userString'] = {name: transUser, value: userColumnDisplay}
    this.columns['myEntries']['creationDateString'] = {name: transDate, value: dateColumnDisplay}
    this.columnsKeys['entries'].push('title')
    this.columnsKeys['myEntries'].push('title')
    this.columnsKeys['myEntries'].push('creationDateString')
    this.columnsKeys['managerEntries'].push('title')
    this.columnsKeys['managerEntries'].push('creationDateString')
    this.columnsKeys['managerEntries'].push('userString')
    this.fieldsColumns['entries'].push(alertFieldColumn)
    this.fieldsColumns['entries'].push(titleFieldColumn)
    this.fieldsColumns['myEntries'].push(alertFieldColumn)
    this.fieldsColumns['myEntries'].push(titleFieldColumn)
    this.fieldsColumns['myEntries'].push(dateFieldColumn)
    this.fieldsColumns['managerEntries'].push(alertFieldColumn)
    this.fieldsColumns['managerEntries'].push(titleFieldColumn)
    this.fieldsColumns['managerEntries'].push(dateFieldColumn)
    this.fieldsColumns['managerEntries'].push(userFieldColumn)

    if (displayMetadata)  {
      this.columns['entries']['creationDateString'] = {name: transDate, value: dateColumnDisplay}
      this.columns['entries']['userString'] = {name: transUser, value: userColumnDisplay}
      this.columnsKeys['entries'].push('creationDateString')
      this.columnsKeys['entries'].push('userString')
      this.fieldsColumns['entries'].push(dateFieldColumn)
      this.fieldsColumns['entries'].push(userFieldColumn)
    }
    if (this.config['display_categories']) {
      this.columns['entries']['categoriesString'] = {name: transCategories, value: categoriesColumnDisplay}
      this.columns['myEntries']['categoriesString'] = {name: transCategories, value: categoriesColumnDisplay}
      this.columns['managerEntries']['categoriesString'] = {name: transCategories, value: categoriesColumnDisplay}
      this.columnsKeys['entries'].push('categoriesString')
      this.columnsKeys['myEntries'].push('categoriesString')
      this.columnsKeys['managerEntries'].push('categoriesString')
      const categoriesFieldColumns = {
        id: 'categoriesString',
        title: transCategories,
        filter: {categoriesString: 'text'},
        sortable: 'categoriesString'
      }
      this.fieldsColumns['entries'].push(categoriesFieldColumns)
      this.fieldsColumns['myEntries'].push(categoriesFieldColumns)
      this.fieldsColumns['managerEntries'].push(categoriesFieldColumns)
    }
    if (this.config['display_keywords']) {
      this.columns['entries']['keywordsString'] = {name: transKeywords, value: keywordsColumnDisplay}
      this.columns['myEntries']['keywordsString'] = {name: transKeywords, value: keywordsColumnDisplay}
      this.columns['managerEntries']['keywordsString'] = {name: transKeywords, value: keywordsColumnDisplay}
      this.columnsKeys['entries'].push('keywordsString')
      this.columnsKeys['myEntries'].push('keywordsString')
      this.columnsKeys['managerEntries'].push('keywordsString')
      const keywordsFieldColumns = {
        id: 'keywordsString',
        title: transKeywords,
        filter: {keywordsString: 'text'},
        sortable: 'keywordsString'
      }
      this.fieldsColumns['entries'].push(keywordsFieldColumns)
      this.fieldsColumns['myEntries'].push(keywordsFieldColumns)
      this.fieldsColumns['managerEntries'].push(keywordsFieldColumns)
    }
    this.fields.forEach(f => {
      if (!f['hidden']) {
        const id = f['id']
        let data = {id: id, title: f['name']}

        if (f['type'] === 3) {
          data['sortable'] = `${id}`
        } else {
          data['sortable'] = `field_${id}`
        }
        data['filter'] = {['field_' + id]: 'text'}
        const columnDisplay = this.checkColumnDefaultValue(id)
        this.columns['myEntries'][id] = {name: f['name'], value: columnDisplay}
        this.columns['managerEntries'][id] = {name: f['name'], value: columnDisplay}
        this.columnsKeys['myEntries'].push(id)
        this.columnsKeys['managerEntries'].push(id)
        this.fieldsColumns['myEntries'].push(data)
        this.fieldsColumns['managerEntries'].push(data)

        if (f['type'] !== 9 && (displayMetadata || !f['isMetadata'])) {
          this.columns['entries'][id] = {name: f['name'], value: columnDisplay}
          this.columnsKeys['entries'].push(id)
          this.fieldsColumns['entries'].push(data)
        }
      }
    })
    this.columns['managerEntries']['actions'] = {name: transActions, value: actionsColumnDisplay}
    this.columnsKeys['managerEntries'].push('actions')
    this.fieldsColumns['managerEntries'].push({id: 'actions', title: transActions})

    if (this.canEdit()) {
      this.columns['entries']['actions'] = {name: transActions, value: actionsColumnDisplay}
      this.columnsKeys['entries'].push('actions')
      this.fieldsColumns['entries'].push({id: 'actions', title: transActions})
    }
    if (this.canEdit() || this.config['edition_enabled']) {
      this.columns['myEntries']['actions'] = {name: transActions, value: actionsColumnDisplay}
      this.columnsKeys['myEntries'].push('actions')
      this.fieldsColumns['myEntries'].push({id: 'actions', title: transActions})
    }
  }

  isAnon() {
    return this.ClacoFormService.getIsAnon()
  }

  canEdit() {
    return this.ClacoFormService.getCanEdit()
  }

  getStatusClass(status) {
    let statusClass = ''

    if (status === 0) {
      statusClass = 'pending-entry-row'
    } else if (status === 2) {
      statusClass = 'unpublished-entry-row'
    }

    return statusClass
  }

  getDisplayedColumns(type) {
    let columns = []

    this.fieldsColumns[type].forEach(fc => {
      if (fc['id'] === 'alert') {
        columns.push(fc)
      } else {
        if (this.columns[type][fc['id']]['value']) {
          columns.push(fc)
        }
      }
    })

    return columns
  }

  isColumnDisplayed(type, name) {
    return this.columns[type] && this.columns[type][name] && this.columns[type][name]['value']
  }

  isDefaultField(name) {
    return name === 'title' ||
      name === 'userString' ||
      name === 'creationDateString' ||
      name === 'categoriesString' ||
      name === 'keywordsString'
  }

  isCustomField(name) {
    return name !== 'alert' &&
      name !== 'actions' &&
      name !== 'title' &&
      name !== 'userString' &&
      name !== 'creationDateString' &&
      name !== 'categoriesString' &&
      name !== 'keywordsString'
  }

  deleteEntry(entry) {
    this.EntryService.deleteEntry(entry, this._removeEntryCallback)
  }

  changeEntryStatus(entry) {
    this.EntryService.changeEntryStatus(entry, this._updateEntryCallback)
  }

  downloadPdf(entryId) {
    this.EntryService.downloadPdf(entryId)
  }

  canGeneratePdf() {
    return this.ClacoFormService.getCanGeneratePdf()
  }

  checkColumnDefaultValue(value) {
    if (this.config['search_columns']) {
      return this.config['search_columns'].findIndex(sc => sc === value) > -1
    } else {
      return ['title', 'creationDateString', 'userString', 'categoriesString', 'keywordsString', 'actions'].findIndex(sc => sc === value) > -1
    }
  }
}