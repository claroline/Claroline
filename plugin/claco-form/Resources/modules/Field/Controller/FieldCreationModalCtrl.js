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
    this.FieldService = FieldService
    this.CategoryService = CategoryService
    this.resourceId = resourceId
    this.title = title
    this.callback = callback
    this.field = {
      name: null,
      type: null,
      required: true,
      isMetadata: false,
      locked: false,
      lockedEditionOnly: false,
      hidden: false
    }
    this.fieldErrors = {
      name: null
    }
    this.types = FieldService.getTypes()
    this.type = this.types[0]
    this.index = 1
    this.choices = [{index: this.index, value: '', category: null, categoryEnabled: false, cascadeEnabled: false, new: true}]
    this.choicesErrors = {}
    this.choicesErrors[this.index] = null
    this.choicesChildren = {}
    this.choicesChildrenErrors = {}
    this.categories = CategoryService.getCategories()
    this.currentParentIndex = null
    this.currentParent = null
    this.cascadeLevelMax = FieldService.getCascadeLevelMax()
    this.selectFields = []
    this.selectFieldToCopy = null
    ++this.index
    this.initializeSelectFields()
  }

  initializeSelectFields() {
    const fields = this.FieldService.getFields()
    fields.forEach(f => {
      if (f['type'] === 5) {
        this.selectFields.push(f)
      }
    })
  }

  checkValues() {
    if (this.field['locked'] && !this.field['lockedEditionOnly']) {
      this.field['required'] = false
    }
  }

  submit() {
    this.resetErrors()
    this.currentMode = 'button'
    this.selectFieldToCopy = null

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
      for (const parentIndex in this.choicesChildren) {
        this.choicesChildren[parentIndex].forEach(c => {
          if (!c['value']) {
            this.choicesChildrenErrors[c['index']] = Translator.trans('form_not_blank_error', {}, 'clacoform')
          } else {
            this.choicesChildren[parentIndex].forEach(nc => {
              if ((nc['index'] !== c['index']) && (nc['value'] === c['value'])) {
                this.choicesChildrenErrors[c['index']] = Translator.trans('form_not_unique_error', {}, 'clacoform')
                this.choicesChildrenErrors[nc['index']] = Translator.trans('form_not_unique_error', {}, 'clacoform')
              }
            })
          }
        })
      }
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
            this.$http.post(url, {fieldData: this.field, choicesData: this.choices, choicesChildrenData: this.choicesChildren}).then(d => {
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
    for (const key in this.choicesChildrenErrors) {
      this.choicesChildrenErrors[key] = null
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
    if (valid) {
      for (const key in this.choicesChildrenErrors) {
        if (this.choicesChildrenErrors[key]) {
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
    this.choices.push({
      index: this.index,
      value: '',
      category: null,
      categoryEnabled: false,
      cascadeEnabled: false,
      new: true
    })
    this.choicesErrors[this.index] = null
    ++this.index
  }

  removeChoice(index) {
    const choiceIndex = this.choices.findIndex(c => c['index'] === index)

    if (choiceIndex > -1) {
      this.choices.splice(choiceIndex, 1)
      delete this.choicesErrors[index]
      this.removeAllChildren(index)
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

  switchChoiceCascade(index) {
    this.currentMode = 'button'
    this.selectFieldToCopy = null

    if (index === this.currentParentIndex) {
      this.currentParentIndex = null
      this.currentParent['cascadeEnabled'] = false
      this.currentParent = null
    } else {
      this.closeAllCascades()
      const choiceIndex = this.choices.findIndex(c => c['index'] === index)

      if (choiceIndex > -1) {
        this.choices[choiceIndex]['cascadeEnabled'] = true
        this.currentParent = this.choices[choiceIndex]
        this.currentParentIndex = index
      }
    }
  }

  switchChildChoiceCascade(parentIndex, index) {
    this.currentMode = 'button'
    this.selectFieldToCopy = null

    if (index === this.currentParentIndex) {
      this.currentParentIndex = null
      this.currentParent['cascadeEnabled'] = false
      this.currentParent = null
    } else {
      this.closeRelativeCascades(parentIndex, index)
      const choiceIndex = this.choicesChildren[parentIndex].findIndex(c => c['index'] === index)

      if (choiceIndex > -1) {
        this.choicesChildren[parentIndex][choiceIndex]['cascadeEnabled'] = true
        this.currentParentIndex = index
        this.currentParent = this.choicesChildren[parentIndex][choiceIndex]
      }
    }
  }

  closeRelativeCascades(parentIndex, index) {
    this.choicesChildren[parentIndex].forEach(c => c['cascadeEnabled'] = false)

    if (this.choicesChildren[index]) {
      this.choicesChildren[index].forEach(c => c['cascadeEnabled'] = false)
    }
  }

  closeAllCascades() {
    this.choices.forEach(c => c['cascadeEnabled'] = false)

    for (const parentId in this.choicesChildren) {
      this.choicesChildren[parentId].forEach(c => c['cascadeEnabled'] = false)
    }
    this.currentParentIndex = null
    this.currentParent = null
  }

  addChildChoice(parentIndex, value = '') {
    if (!this.choicesChildren[parentIndex]) {
      this.choicesChildren[parentIndex] = []
    }
    this.choicesChildren[parentIndex].push({
      index: this.index,
      value: value,
      category: null,
      categoryEnabled: false,
      cascadeEnabled: false,
      new: true
    })
    this.choicesChildrenErrors[this.index] = null
    ++this.index
  }

  removeChildChoice(parentIndex, index) {
    const choiceIndex = this.choicesChildren[parentIndex].findIndex(c => c['index'] === index)

    if (choiceIndex > -1) {
      this.choicesChildren[parentIndex].splice(choiceIndex, 1)
      delete this.choicesErrors[index]
      this.removeAllChildren(index)
    }
  }

  enableChildChoiceCategory(parentIndex, index) {
    const choiceIndex = this.choicesChildren[parentIndex].findIndex(c => c['index'] === index)

    if (choiceIndex > -1) {
      this.choicesChildren[parentIndex][choiceIndex]['categoryEnabled'] = true
    }
  }

  disableChildChoiceCategory(parentIndex, index) {
    const choiceIndex = this.choicesChildren[parentIndex].findIndex(c => c['index'] === index)

    if (choiceIndex > -1) {
      this.choicesChildren[parentIndex][choiceIndex]['categoryEnabled'] = false
      this.choicesChildren[parentIndex][choiceIndex]['category'] = null
    }
  }

  removeAllChildren(index) {
    if (this.choicesChildren[index]) {
      this.choicesChildren[index].forEach(c => this.removeAllChildren(c['index']))
      delete this.choicesChildren[index]
    }
  }

  showSelectLists() {
    this.currentMode = 'list'
    this.selectFieldToCopy = null
  }

  cancelListCopy() {
    this.currentMode = 'button'
    this.selectFieldToCopy = null
  }

  copyList(parentIndex) {
    const allChoices = this.selectFieldToCopy['fieldFacet'] && this.selectFieldToCopy['fieldFacet']['field_facet_choices'] ?
      this.selectFieldToCopy['fieldFacet']['field_facet_choices'] :
      []
    allChoices.forEach(c => {
      if (!c['parent']) {
        this.addChildChoice(parentIndex, c['label'])
      }
    })
    this.currentMode = 'button'
    this.selectFieldToCopy = null
  }
}
