/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Translator*/

export default class EntryEditionCtrl {
  constructor($state, $stateParams, ClacoFormService, EntryService, FieldService, CategoryService, KeywordService) {
    this.$state = $state
    this.ClacoFormService = ClacoFormService
    this.EntryService = EntryService
    this.CategoryService = CategoryService
    this.KeywordService = KeywordService
    this.entryId = parseInt($stateParams.entryId)
    this.source = {}
    this.entry = {}
    this.entryTitle = {label: Translator.trans('entry_title', {}, 'clacoform'), value: null}
    this.entryErrors = {}
    this.resourceId = ClacoFormService.getResourceId()
    this.title = Translator.trans('entry_edition', {}, 'clacoform')
    this.config = ClacoFormService.getResourceDetails()
    this.template = ClacoFormService.getTemplate()
    this.fields = FieldService.getFields()
    this.mode = 'edition'
    this.categoriesChoices = []
    this.categories = []
    this.selectedCategory = null
    this.showCategoriesSelect = false
    this.keywordssChoices = []
    this.keywords = []
    this.selectedKeyword = null
    this.showKeywordsSelect = false
    this.initialize()
  }

  initialize() {
    this.ClacoFormService.clearMessages()
    this.source = this.EntryService.getEntry(this.entryId)

    if (this.source === undefined) {
      this.cancel()
    } else {
      this.initializeEntry()
      this.initializeCategories()
      this.initializeKeywords()
    }
    this.initializeTemplate()
  }

  initializeEntry() {
    this.entryTitle['value'] = this.source['title']
    this.fields.forEach(f => {
      if (!f['hidden']) {
        const id = f['id']
        this.entry[id] = this.source[id] !== undefined ? this.source[id] : null

        if (f['required']) {
          this.entryErrors[id] = null
        }
      }
    })
  }

  initializeCategories() {
    this.categoriesChoices = this.CategoryService.getCategories()
    this.source['categories'].forEach(c => this.categories.push(c))
  }

  initializeKeywords() {
    this.keywordsChoices = this.KeywordService.getKeywords()
    this.source['keywords'].forEach(k => this.keywords.push(k['name']))
  }

  initializeTemplate() {
    if (this.template) {
      const replacedTitleField = `
        <form-field field="['${this.entryTitle['label']}',
                           'text',
                           {error: cfc.entryErrors['entry_title'], noLabel: true}]"
                    ng-model="cfc.entryTitle['value']"
        >
        </form-field>
      `
      this.template = this.template.replace('%clacoform_entry_title%', replacedTitleField)
      this.fields.forEach(f => {
        if (!f['hidden']) {
          const id = f['id']
          const name = f['name'].replace(/'/g, ' ')
          const disabled = this.isFieldDisabled(f)

          if (this.template) {
            let choices = JSON.stringify(f['fieldFacet']['field_facet_choices'])
            choices = choices.replace(/'/g, '\\\'')
            choices = choices.replace(/"/g, '\'')
            const replacedField = `
              <form-field field="['${name}',
                                 '${f['fieldFacet']['translation_key']}',
                                 {
                                     values: ${choices},
                                     choice_value: 'value',
                                     error: cfc.entryErrors[${id}],
                                     noLabel: true,
                                     disabled: ${disabled}
                                 }]"
                          ng-model="cfc.entry[${id}]"
              >
              </form-field>
            `
            this.template = this.template.replace(`%${this.ClacoFormService.removeAccent(name)}%`, replacedField)
          }
        }
      })
    }
  }

  canEdit() {
    return this.ClacoFormService.getCanEdit()
  }

  isAllowed() {
    return this.EntryService.getCanEditEntry(this.entryId)
  }

  canManageCategories() {
    return this.isAllowed() && this.canEdit()
  }

  canManageKeywords() {
    return this.isAllowed() && this.config['keywords_enabled']
  }

  enableCategoriesSelect() {
    this.showCategoriesSelect = true
  }

  disableCategoriesSelect() {
    this.showCategoriesSelect = false
    this.selectedCategory = null
  }

  addSelectedCategory() {
    if (this.selectedCategory) {
      const existingCategory = this.categories.find(c => c['id'] === this.selectedCategory['id'])

      if (!existingCategory) {
        this.categories.push(this.selectedCategory)
      }
    }
    this.showCategoriesSelect = false
    this.selectedCategory = null
  }

  removeCategory(category) {
    const index = this.categories.findIndex(c => c['id'] === category['id'])

    if (index > -1) {
      this.categories.splice(index, 1)
    }
  }

  enableKeywordsSelect() {
    this.showKeywordsSelect = true
  }

  disableKeywordsSelect() {
    this.showKeywordsSelect = false
    this.selectedKeyword = null
  }

  addSelectedKeyword() {
    if (this.selectedKeyword) {
      if (this.config['new_keywords_enabled']) {
        const existingKeyword = this.keywords.find(k => k.toUpperCase() === this.selectedKeyword.toUpperCase())

        if (!existingKeyword) {
          this.keywords.push(this.selectedKeyword)
        }
      } else {
        const existingKeyword = this.keywords.find(k => k.toUpperCase() === this.selectedKeyword['name'].toUpperCase())

        if (!existingKeyword) {
          this.keywords.push(this.selectedKeyword['name'])
        }
      }
    }
    this.showKeywordsSelect = false
    this.selectedKeyword = null
  }

  removeKeyword(keyword) {
    const index = this.keywords.findIndex(k => k === keyword)

    if (index > -1) {
      this.keywords.splice(index, 1)
    }
  }

  isFieldDisabled(field) {
    return !this.canEdit() && field['locked'] && (
      (['user', 'all'].indexOf(this.config['locked_fields_for']) > -1 && !this.EntryService.isManagerEntry(this.entryId)) ||
      (['manager', 'all'].indexOf(this.config['locked_fields_for']) > -1 && this.EntryService.isManagerEntry(this.entryId))
    )
  }

  submit() {
    this.resetErrors()

    if (!this.entryTitle['value']) {
      this.entryErrors['entry_title'] = Translator.trans('form_not_blank_error', {}, 'clacoform')
    }
    this.fields.forEach(f => {
      if (!f['hidden']) {
        const id = f['id']

        if (f['required'] && (this.entry[id] === undefined || this.entry[id] === null || this.entry[id] === '' || this.entry[id].length === 0)) {
          this.entryErrors[id] = Translator.trans('form_not_blank_error', {}, 'clacoform')
          this.entry[id] = null
        }
      }
    })

    if (this.isValid()) {
      let categoriesIds = []
      this.categories.forEach(c => categoriesIds.push(c['id']))
      this.EntryService.editEntry(this.source['id'], this.entry, this.entryTitle['value'], categoriesIds, this.keywords).then(d => {
        if (d !== 'null') {
          this.$state.go('entries_list')
        }
      })
    }
  }

  resetErrors() {
    for (const key in this.entryErrors) {
      this.entryErrors[key] = null
    }
  }

  isValid() {
    let valid = true

    for (const key in this.entryErrors) {
      if (this.entryErrors[key]) {
        valid = false
        break
      }
    }

    return valid
  }

  cancel() {
    this.$state.go('entries_list')
  }
}
