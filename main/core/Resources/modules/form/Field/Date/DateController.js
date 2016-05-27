import FieldController from '../FieldController'

export default class DateController extends FieldController {
  constructor () {
    super()
    this.options = {}
    this.parseNgModel()
    this.opened = false
  }

  getDateFormat () {
    return Translator.trans('date_form_format', {}, 'platform')
  }

  parseNgModel () {
    this.ngModel = !this.ngModel ? new Date() : new Date(this.ngModel)
  }

  open () {
    this.opened = true
  }
}
