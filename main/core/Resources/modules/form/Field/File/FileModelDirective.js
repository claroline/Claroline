//https://github.com/ghostbar/angular-file-model/blob/master/angular-file-model.js

export default class FileModelDirective {
  constructor ($parse) {
    this.restrict = 'A'
    this.$parse = $parse
  }

  compile (tElem, tAttrs) {
    return this.postLinkFn.bind(this)
  }

  postLinkFn (scope, element, attrs) {
    var model = this.$parse(attrs.fileModel)
    var modelSetter = model.assign

    element.bind('change', () => {
      scope.$apply(() => {
        if (attrs.multiple) {
          modelSetter(scope, element[0].files)
      } else {
          modelSetter(scope, element[0].files[0])
        }
      })
    })
  }
}
