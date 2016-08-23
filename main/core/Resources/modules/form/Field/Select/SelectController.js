import FieldController from '../FieldController'

export default class SelectController extends FieldController {
  constructor () {
    super()
    if (this.field[2].default && !this.ngModel) { this.ngModel = this.field[2].default }
    this._ngModel = this.ngModel
    this.choice_name = this.field[2].choice_name || 'label'
    this.choice_value = this.field[2].choice_value || 'value'
  }
}
