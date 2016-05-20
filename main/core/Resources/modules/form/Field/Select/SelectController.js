import FieldController from '../FieldController'

export default class SelectController extends FieldController {
  constructor () {
    super()
    if (this.field[2].default && !this.ngModel) { this.ngModel = this.field[2].default }
    this._ngModel = this.ngModel
  }
}
