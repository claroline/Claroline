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
    this.FieldService = FieldService
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
      locked: false,
      lockedEditionOnly: false,
      hidden: false
    }
    this.fieldErrors = {
      name: null
    }
    this.types = FieldService.getTypes()
    this.type = null
    this.index = 1
    this.choices = []
    this.choicesErrors = {}
    this.choicesChildren = {}
    this.choicesChildrenErrors = {}
    this.categories = CategoryService.getCategories()
    this.currentParentIndex = null
    this.currentParent = null
    this.currentMode = 'button'
    this.cascadeLevelMax = FieldService.getCascadeLevelMax()
    this.selectFields = []
    this.selectFieldToCopy = null
    this.initializeField()
    this.initializeChoices()
    this.initializeSelectFields()
  }

  initializeField() {
    this.field['name'] = this.source['name']
    this.field['type'] = this.source['type']
    this.field['required'] = this.source['required']
    this.field['isMetadata'] = this.source['isMetadata']
    this.field['locked'] = this.source['locked']
    this.field['lockedEditionOnly'] = this.source['lockedEditionOnly']
    this.field['hidden'] = this.source['hidden']
    const selectedType = this.types.find(t => t['value'] === this.source['type'])
    this.type = selectedType
  }

  initializeChoices() {
    if (this.source['fieldFacet']['field_facet_choices'].length > 0) {
      this.source['fieldFacet']['field_facet_choices'].forEach(c => {
        const id = c['id']

        if (id >= this.index) {
          this.index = id + 1
        }
        if (c['parent']) {
          const parentId = c['parent']['id']

          if (!this.choicesChildren[parentId]) {
            this.choicesChildren[parentId] = []
          }
          this.choicesChildren[parentId].push({
            index: id,
            value: c['label'],
            category: null,
            categoryEnabled: false,
            cascadeEnabled: false,
            new: false
          })
          this.choicesChildrenErrors[id] = null
        } else {
          this.choices.push({
            index: id,
            value: c['label'],
            category: null,
            categoryEnabled: false,
            cascadeEnabled: false,
            new: false
          })
          this.choicesErrors[id] = null
        }
      })
      const choicesCategoriesUrl = Routing.generate('claro_claco_form_field_choices_categories_retrieve', {field: this.source['id']})
      this.$http.get(choicesCategoriesUrl).then(d => {
        if (d['status'] === 200) {
          const choicesCategories = JSON.parse(d['data'])
          choicesCategories.forEach(cc => {
            const id = cc['fieldFacetChoice']['id']
            const choiceIndex = this.choices.findIndex(oc => oc['index'] === id)

            if (choiceIndex > -1) {
              const selectedCategory = this.categories.find(c => c['id'] === cc['category']['id'])

              if (selectedCategory) {
                this.choices[choiceIndex]['category'] = selectedCategory
                this.choices[choiceIndex]['categoryEnabled'] = true
              }
            } else {
              let found = false

              for (const parentId in this.choicesChildren) {
                if (!found) {
                  const childChoiceIndex = this.choicesChildren[parentId].findIndex(oc => oc['index'] === id)

                  if (childChoiceIndex > -1) {
                    found = true
                    const selectedCategory = this.categories.find(c => c['id'] === cc['category']['id'])

                    if (selectedCategory) {
                      this.choicesChildren[parentId][childChoiceIndex]['category'] = selectedCategory
                      this.choicesChildren[parentId][childChoiceIndex]['categoryEnabled'] = true
                    }
                  }
                }
              }
            }
          })
        }
      })
    } else {
      this.choices = [{index: this.index, value: '', category: null, categoryEnabled: false, cascadeEnabled: false, new: true}]
      this.choicesErrors[this.index] = null
      ++this.index
    }
  }

  initializeSelectFields() {
    const fields = this.FieldService.getFields()
    fields.forEach(f => {
      if (f['type'] === 5 && f['id'] !== this.source['id']) {
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
        {clacoForm: this.resourceId, name: this.field['name'], id: this.source['id']}
      )
      this.$http.get(checkNameUrl).then(d => {
        if (d['status'] === 200) {
          if (d['data'] === 'null') {
            const url = Routing.generate('claro_claco_form_field_edit', {field: this.source['id']})
            this.$http.put(url, {fieldData: this.field, choicesData: this.choices, choicesChildrenData: this.choicesChildren}).then(d => {
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
