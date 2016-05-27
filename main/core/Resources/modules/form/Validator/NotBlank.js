export default class NotBlank {
  validate (el) {
    return !(el === undefined || el === '' || el === null)
  }

  getErrorMessage (el) {
    return Translator.trans('value_not_blank', {}, 'validators')
  }
}
