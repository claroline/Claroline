import langs from './iso.js'

export default class LangController {
  constructor () {
    this.langField = angular.copy(this.field)
    this.langField[1] = 'select'
    this.langField[2] = this.langField[2] || {}
    this.langField[2].values = this.getOptions()
  }

  getOptions () {
    const values = []

    Object.keys(langs).forEach(key => {
      values.push({'label': langs[key].nativeName, 'value': key})
    })

    return values
  }
}
