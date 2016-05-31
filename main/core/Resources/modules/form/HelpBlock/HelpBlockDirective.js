
export default class HelpBlockDirective {
  constructor ($parse, $compile) {
    this.scope = {}
    this.$compile = $compile
    this.$parse = $parse
    this.restrict = 'E'
    this.replace = true
    this.require = '^ngModel'
    this.controller = () => {}
    this.controllerAs = 'hc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }

  compile (tElem, tAttrs) {
    return this.postLinkFn.bind(this)
  }

  postLinkFn (scope, elm, attrs, ngModel) {
    const field = this.$parse(attrs.field)(scope.$parent)
    const options = field[2]
    ngModel.formValidators = {}

    if (options && options.validators) {
      options.validators.forEach(validator => {
        ngModel.formValidators[validator.constructor.name] = validator
        ngModel.$validators[validator.constructor.name] = modelValue => validator.validate(modelValue)
      })
    }

    scope.$watch(() => {
      return ngModel.$modelValue
    }, newValue => {
      elm.html('')
      Object.keys(ngModel.$error).forEach(validator => {
        elm.append(this.buildHelpBlock(ngModel.formValidators[validator].getErrorMessage()))
      })
    })
  }

  buildHelpBlock (message) {
    return `<p class="help-block">${message}</p>`
  }
}
