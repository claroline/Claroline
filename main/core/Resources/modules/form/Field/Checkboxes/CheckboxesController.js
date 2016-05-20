import FieldController from '../FieldController'

export default class CheckboxesController extends FieldController {
  constructor () {
    super()
    if (!this.ngModel) this.ngModel = []
  }
}
