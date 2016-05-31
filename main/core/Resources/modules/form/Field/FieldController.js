export default class FieldController {
  constructor () {
    this.name = this.field[0]
    this.options = this.field[2] || {}
    this.label = this.options.label !== undefined ? this.options.label : this.name
    this.translationDomain = this.options.translation_domain || 'platform'
  }
}
