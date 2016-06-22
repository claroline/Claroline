export default class HelpBlockDirective {
  constructor ($parse, $compile) {
    this.scope = {}
    this.$compile = $compile
    this.$parse = $parse
    this.restrict = 'E'
    this.replace = true
    this.require = '^ngModel'
    this.showErrors = true
    this.controller = () => {
    }
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
    scope.$watch(() => {
      return this.$parse(attrs.field)(scope.$parent)
    }, field => {
      const options = field[2]
      if (options) this.showErrors = options.show_errors === undefined ? true: options.show_errors
      ngModel.formValidators = {}

      if (options && options.validators) {
        options.validators.forEach(validator => {
          ngModel.formValidators[validator.constructor.name] = validator
          ngModel.$validators[validator.constructor.name] = modelValue => validator.validate(modelValue)
        })
      }

      ngModel.$validate()
      this.addErrors(ngModel, elm)
    })

    scope.$watch(() => {
      return ngModel.$modelValue
    }, newValue => {
      this.addErrors(ngModel, elm)
    })
  }

  addErrors (ngModel, elm) {
    elm.html('')
    if (this.showErrors) {
        Object.keys(ngModel.$error).forEach(validator => {
          if (ngModel.formValidators[validator]) {
            elm.append(this.buildHelpBlock(ngModel.formValidators[validator].getErrorMessage()))
          }
        })
    }
  }

  buildHelpBlock (message) {
    return `<p class="help-block">${message}</p>`
  }
}
