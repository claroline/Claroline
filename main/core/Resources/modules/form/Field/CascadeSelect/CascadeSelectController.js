import FieldController from '../FieldController'

export default class CascadeSelectController extends FieldController {
  constructor() {
    super()

    if (!this.ngModel) {
      this.ngModel = []
    }
    this._ngModel = []
    this.choice_name = this.field[2].choice_name || 'label'
    this.choice_value = this.field[2].choice_value || 'value'
    this.choices = []
    this.initializeChoicesStructure(this.field[2].values)
    this.initializeModel()
  }

  initializeChoicesStructure(values) {
    values.forEach(v => {
      const level = this.computeLevel(v)

      if (level === 0) {
        if (!this.choices[level]) {
          this.choices[level] = []
        }
        this.choices[level].push(v)
      } else {
        const parentId = v['parent']['id']

        if (!this.choices[level]) {
          this.choices[level] = {}
        }
        if (!this.choices[level][parentId]) {
          this.choices[level][parentId] = []
        }
        this.choices[level][parentId].push(v)
      }
    })
  }

  initializeModel() {
    const modelLevel = this.ngModel.length - 1

    if (modelLevel === 0 ) {
      const choice = this.choices[modelLevel].find(c => c['value'] === this.ngModel[modelLevel])
      this._ngModel[modelLevel] = choice ? choice : null
    } else {
      const choices = []

      for (const parentId in this.choices[modelLevel]) {
        this.choices[modelLevel][parentId].forEach(c => {
          if (c['value'] === this.ngModel[modelLevel]) {
            choices.push(c)
          }
        })
      }
      choices.forEach(c => {
        if (this.isValidChoiceModel(c, modelLevel)) {
          this.retrieveModelValues(c, modelLevel)

          return
        }
      })
    }
  }

  isValidChoiceModel(choice, level) {
    let currentLevel = level
    let currentChoice = choice

    while (currentLevel >= 0) {
      if (currentChoice['label'] === this.ngModel[currentLevel]) {
        --currentLevel

        if (currentChoice['parent']) {
          currentChoice = currentChoice['parent']
        } else {
          break
        }
      } else {
        break
      }
    }

    return currentLevel === -1
  }

  retrieveModelValues(choice, level) {
    this._ngModel[level] = choice
    --level

    while (level >= 0) {
      choice = choice['parent']
      let choiceModel = null

      if (level === 0) {
        choiceModel = this.choices[level].find(c => c['id'] === choice['id'])
      } else {
        const parentId = choice['parent']['id']
        choiceModel = this.choices[level][parentId].find(c => c['id'] === choice['id'])
      }
      this._ngModel[level] = choiceModel ? choiceModel : null
      --level
    }
  }

  computeLevel(value) {
    let level = 0
    let current = value

    while (current['parent']) {
      ++level
      current = current['parent']
    }

    return level
  }

  changeSelection(level) {
    this.ngModel = []
    let count = this._ngModel.length - (level + 1)
    this._ngModel.splice(level + 1, count)

    if (!this.choices[level + 1] || !this.choices[level + 1][this._ngModel[level]['id']]) {
      for (let i = 0; i < this._ngModel.length; i++) {
        this.ngModel[i] = this._ngModel[i] ? this._ngModel[i]['value'] : this._ngModel[i]
      }
    }
  }
}
