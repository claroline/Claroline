/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Translator*/

export default class GeneralConfigurationCtrl {
  constructor($state, ClacoFormService, CategoryService, EntryService, FieldService) {
    this.$state = $state
    this.ClacoFormService = ClacoFormService
    this.EntryService = EntryService
    this.resourceId = ClacoFormService.getResourceId()
    this.config = ClacoFormService.getResourceDetails()
    this.configErrors = {max_entries: null}
    this.isCollapsed = {
      general: false,
      display: true,
      random: true,
      find: true,
      data: true,
      locked: true,
      categories: true,
      comments: true,
      votes: true,
      keywords: true
    }
    this.randomStartDate = {format: 'dd/MM/yyyy', open: false}
    this.randomEndDate = {format: 'dd/MM/yyyy', open: false}
    this.votesStartDate = {format: 'dd/MM/yyyy', open: false}
    this.votesEndDate = {format: 'dd/MM/yyyy', open: false}
    this.dateOptions = {formatYear: 'yy', startingDay: 1, placeHolder: 'jj/mm/aaaa'}
    this.categoriesList = CategoryService.getCategories()
    this.categories = []
    this.fields = FieldService.getFields()
    this.searchColumnsList = [
      {name: Translator.trans('title', {}, 'platform'), id: 'title'},
      {name: Translator.trans('date', {}, 'platform'), id: 'creationDateString'},
      {name: Translator.trans('user', {}, 'platform'), id: 'userString'},
      {name: Translator.trans('categories', {}, 'platform'), id: 'categoriesString'},
      {name: Translator.trans('keywords', {}, 'clacoform'), id: 'keywordsString'},
      {name: Translator.trans('actions', {}, 'platform'), id: 'actions'}
    ]
    this.searchColumns = []
    this.initialize()
  }

  initialize() {
    this.ClacoFormService.clearMessages()

    if (this.config['random_start_date']) {
      this.config['random_start_date'] = new Date(this.config['random_start_date'])
    }
    if (this.config['random_end_date']) {
      this.config['random_end_date'] = new Date(this.config['random_end_date'])
    }
    if (this.config['votes_start_date']) {
      this.config['votes_start_date'] = new Date(this.config['votes_start_date'])
    }
    if (this.config['votes_end_date']) {
      this.config['votes_end_date'] = new Date(this.config['votes_end_date'])
    }
    if (this.config['random_categories']) {
      this.config['random_categories'].forEach(categoryId => {
        const selectedCategory = this.categoriesList.find(c => c['id'] === categoryId)
        this.categories.push(selectedCategory)
      })
    }
    this.fields.forEach(f => this.searchColumnsList.push(f))

    if (this.config['search_columns']) {
      this.config['search_columns'].forEach(value => {
        const column = this.searchColumnsList.find(sc => sc['id'] === value)
        this.searchColumns.push(column)
      })
    } else {
      this.searchColumnsList.forEach(f => {
        if (['title', 'creationDateString', 'userString', 'categoriesString', 'keywordsString', 'actions'].findIndex(key => key === f['id']) > -1) {
          this.searchColumns.push(f)
        }
      })
    }
  }

  canEdit() {
    return this.ClacoFormService.getCanEdit()
  }

  submit() {
    this.resetErrors()

    if (this.config['max_entries'] === null || this.config['max_entries'] === undefined) {
      this.configErrors['max_entries'] = Translator.trans('form_not_blank_error', {}, 'clacoform')
    } else {
      this.config['max_entries'] = parseInt(this.config['max_entries'])

      if (this.config['max_entries'] < 0) {
        this.configErrors['max_entries'] = Translator.trans('form_number_superior_error', {value: 0}, 'clacoform')
      }
    }
    this.config['random_categories'] = []
    this.categories.forEach(c => this.config['random_categories'].push(c['id']))
    this.config['search_columns'] = []
    this.searchColumns.forEach(sc => this.config['search_columns'].push(sc['id']))

    if (this.isValid()) {
      this.ClacoFormService.saveConfiguration(this.resourceId, this.config).then(d => {
        if (d) {
          this.$state.go('menu')
        }
      })
    }
  }

  resetErrors() {
    for (const key in this.configErrors) {
      this.configErrors[key] = null
    }
  }

  isValid() {
    let valid = true

    for (const key in this.configErrors) {
      if (this.configErrors[key]) {
        valid = false
        break
      }
    }

    return valid
  }

  openDatePicker(type) {
    switch (type) {
      case 'randomStart':
        this.randomStartDate['open'] = true
        break
      case 'randomEnd':
        this.randomEndDate['open'] = true
        break
      case 'votesStart':
        this.votesStartDate['open'] = true
        break
      case 'votesEnd':
        this.votesEndDate['open'] = true
        break
    }
  }

  exportEntries() {
    this.ClacoFormService.exportEntries()
  }

  deleteAllEntries() {
    this.EntryService.deleteAllEntries()
  }
}