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

export default class FieldCreationModalCtrl {
  constructor($http, $uibModalInstance, FieldService, CategoryService, resourceId, title, callback) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.CategoryService = CategoryService
    this.resourceId = resourceId
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
    this.type = this.types[0]
    this.index = 1
    this.choices = [{index: this.index, value: '', category: null, categoryEnabled: false}]
    this.choicesErrors = {}
    this.choicesErrors[this.index] = null
    this.categories = CategoryService.getCategories()
    ++this.index
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
        }
      })
    }
    if (this.isValid()) {
      const checkNameUrl = Routing.generate(
        'claro_claco_form_get_field_by_name_excluding_id',
        {clacoForm: this.resourceId, name: this.field['name'], id: 0}
      )
      this.$http.get(checkNameUrl).then(d => {
        if (d['status'] === 200) {
          if (d['data'] === 'null') {
            const url = Routing.generate('claro_claco_form_field_create', {clacoForm: this.resourceId})
            this.$http.post(url, {fieldData: this.field, choicesData: this.choices}).then(d => {
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

    for (const key in this.choicesErrors) {
      if (this.choicesErrors[key]) {
        valid = false
        break
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

  removeChoice(index) {
    const choiceIndex = this.choices.findIndex(c => c['index'] === index)

    if (choiceIndex > -1) {
      this.choices.splice(choiceIndex, 1)
      delete this.choicesErrors[index]
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
