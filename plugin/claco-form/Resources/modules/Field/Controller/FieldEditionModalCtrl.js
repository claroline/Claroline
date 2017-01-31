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

export default class FieldEditionModalCtrl {
  constructor($http, $uibModalInstance, FieldService, CategoryService, resourceId, field, title, callback) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.CategoryService = CategoryService
    this.resourceId = resourceId
    this.source = field
    this.title = title
    this.callback = callback
    this.field = {
      name: null,
      type: null,
      required: true,
      isMetadata: false,
      choices: []
    }
    this.fieldErrors = {
      name: null
    }
    this.types = FieldService.getTypes()
    this.type = null
    this.oldChoices = []
    this.oldChoicesErrors = {}
    this.index = 1
    this.choices = []
    this.choicesErrors = {}
    this.categories = CategoryService.getCategories()
    this.initializeField()
    this.initializeChoices()
  }

  initializeField() {
    this.field['name'] = this.source['name']
    this.field['type'] = this.source['type']
    this.field['required'] = this.source['required']
    this.field['isMetadata'] = this.source['isMetadata']
    const selectedType = this.types.find(t => t['value'] === this.source['type'])
    this.type = selectedType
  }

  initializeChoices() {
    if (this.source['fieldFacet']['field_facet_choices'].length > 0) {
      this.source['fieldFacet']['field_facet_choices'].forEach(c => {
        const id = c['id']
        this.oldChoices.push({index: id, value: c['label'], category: null, categoryEnabled: false})
        this.oldChoicesErrors[id] = null
      })
      const choicesCategoriesUrl = Routing.generate('claro_claco_form_field_choices_categories_retrieve', {field: this.source['id']})
      this.$http.get(choicesCategoriesUrl).then(d => {
        if (d['status'] === 200) {
          const choicesCategories = JSON.parse(d['data'])
          choicesCategories.forEach(cc => {
            const id = cc['fieldFacetChoice']['id']
            const oldChoiceIndex = this.oldChoices.findIndex(oc => oc['index'] === id)

            if (oldChoiceIndex > -1) {
              const selectedCategory = this.categories.find(c => c['id'] === cc['category']['id'])

              if (selectedCategory) {
                this.oldChoices[oldChoiceIndex]['category'] = selectedCategory
                this.oldChoices[oldChoiceIndex]['categoryEnabled'] = true
              }
            }
          })
        }
      })
    } else {
      this.choices = [{index: this.index, value: ''}]
      this.choicesErrors[this.index] = null
      ++this.index
    }
  }

  submit() {
    this.resetErrors()

    if (!this.field['name']) {
      this.fieldErrors['name'] = Translator.trans('form_not_blank_error', {}, 'clacoform')
    } else if (this.field['name'] === 'clacoform_entry_title') {
      this.fieldErrors['name'] = Translator.trans('form_reserved_error', {}, 'clacoform')
    }
    this.field['type'] = this.type['value']

    if (this.hasChoices()) {
      this.oldChoices.forEach(c => {
        if (!c['value']) {
          this.oldChoicesErrors[c['index']] = Translator.trans('form_not_blank_error', {}, 'clacoform')
        } else {
          this.oldChoices.forEach(oc => {
            if ((oc['index'] !== c['index']) && (oc['value'] === c['value'])) {
              this.oldChoicesErrors[c['index']] = Translator.trans('form_not_unique_error', {}, 'clacoform')
              this.oldChoicesErrors[oc['index']] = Translator.trans('form_not_unique_error', {}, 'clacoform')
            }
          })
          this.choices.forEach(nc => {
            if (nc['value'] === c['value']) {
              this.oldChoicesErrors[c['index']] = Translator.trans('form_not_unique_error', {}, 'clacoform')
              this.choicesErrors[nc['index']] = Translator.trans('form_not_unique_error', {}, 'clacoform')
            }
          })
        }
      })
      this.choices.forEach(c => {
        if (!c['value']) {
          this.choicesErrors[c['index']] = Translator.trans('form_not_blank_error', {}, 'clacoform')
        } else {
          this.choices.forEach(nc => {
            if ((nc['index'] !== c['index']) && (nc['value'] === c['value'])) {
              this.choicesErrors[c['index']] = Translator.trans('form_not_unique_error', {}, 'clacoform')
              this.choicesErrors[nc['index']] = Translator.trans('form_not_unique_error', {}, 'clacoform')
            }
          })
          this.oldChoices.forEach(oc => {
            if (oc['value'] === c['value']) {
              this.choicesErrors[c['index']] = Translator.trans('form_not_unique_error', {}, 'clacoform')
              this.oldChoicesErrors[oc['index']] = Translator.trans('form_not_unique_error', {}, 'clacoform')
            }
          })
        }
      })
    }
    if (this.isValid()) {
      const checkNameUrl = Routing.generate(
        'claro_claco_form_get_field_by_name_excluding_id',
        {clacoForm: this.resourceId, name: this.field['name'], id: this.source['id']}
      )
      this.$http.get(checkNameUrl).then(d => {
        if (d['status'] === 200) {
          if (d['data'] === 'null') {
            const url = Routing.generate('claro_claco_form_field_edit', {field: this.source['id']})
            this.$http.put(url, {fieldData: this.field, oldChoicesData: this.oldChoices, choicesData: this.choices}).then(d => {
              this.callback(d['data'])
              this.$uibModalInstance.close()
            })
          } else {
            this.fieldErrors['name'] = Translator.trans('form_not_unique_error', {}, 'clacoform')
          }
        }
      })
    }
  }

  resetErrors() {
    for (const key in this.fieldErrors) {
      this.fieldErrors[key] = null
    }
    for (const key in this.oldChoicesErrors) {
      this.oldChoicesErrors[key] = null
    }
    for (const key in this.choicesErrors) {
      this.choicesErrors[key] = null
    }
  }

  isValid() {
    let valid = true

    for (const key in this.fieldErrors) {
      if (this.fieldErrors[key]) {
        valid = false
        break
      }
    }
    if (valid && this.hasChoices()) {
      valid = this.isChoicesValid()
    }

    return valid
  }

  isChoicesValid() {
    let valid = true

    for (const key in this.oldChoicesErrors) {
      if (this.oldChoicesErrors[key]) {
        valid = false
        break
      }
    }
    if (valid) {
      for (const key in this.choicesErrors) {
        if (this.choicesErrors[key]) {
          valid = false
          break
        }
      }
    }

    return valid
  }

  hasChoices() {
    let hasChoice = false

    switch (this.type['value']) {
      case 4 :
      case 5 :
      case 6 :
        hasChoice = true
    }

    return hasChoice
  }

  addChoice() {
    this.choices.push({index: this.index, value: '', category: null, categoryEnabled: false})
    this.choicesErrors[this.index] = null
    ++this.index
  }

  removeOldChoice(index) {
    const choiceIndex = this.oldChoices.findIndex(c => c['index'] === index)

    if (choiceIndex > -1) {
      this.oldChoices.splice(choiceIndex, 1)
      delete this.oldChoicesErrors[index]
    }
  }

  removeChoice(index) {
    const choiceIndex = this.choices.findIndex(c => c['index'] === index)

    if (choiceIndex > -1) {
      this.choices.splice(choiceIndex, 1)
      delete this.choicesErrors[index]
    }
  }

  enableOldChoiceCategory(index) {
    const choiceIndex = this.oldChoices.findIndex(c => c['index'] === index)

    if (choiceIndex > -1) {
      this.oldChoices[choiceIndex]['categoryEnabled'] = true
    }
  }

  disableOldChoiceCategory(index) {
    const choiceIndex = this.oldChoices.findIndex(c => c['index'] === index)

    if (choiceIndex > -1) {
      this.oldChoices[choiceIndex]['categoryEnabled'] = false
      this.oldChoices[choiceIndex]['category'] = null
    }
  }

  enableChoiceCategory(index) {
    const choiceIndex = this.choices.findIndex(c => c['index'] === index)

    if (choiceIndex > -1) {
      this.choices[choiceIndex]['categoryEnabled'] = true
    }
  }

  disableChoiceCategory(index) {
    const choiceIndex = this.choices.findIndex(c => c['index'] === index)

    if (choiceIndex > -1) {
      this.choices[choiceIndex]['categoryEnabled'] = false
      this.choices[choiceIndex]['category'] = null
    }
  }
}
