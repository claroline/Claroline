// heavily inspired from https://github.com/vitalets/checklist-model/blob/master/checklist-model.js for learning purpose
export default class CheckListDirective {
  constructor ($parse, $compile) {
    this.$parse = $parse
    this.$compile = $compile
    this.priority = 1000
    this.scope = true
    this.restrict = 'A'
    this.terminal = true
  }

  compile (tElem, tAttrs) {
    // here we create a default ng-model attribute
    tAttrs.$set('ngModel', 'checked')

    return this.postLinkFn.bind(this)
  }

  add (arr, item) {
    arr = angular.isArray(arr) ? arr : []
    if (!this.contains(arr, item)) arr.push(item)
    return arr
  }

  remove (arr, item) {
    if (this.contains(arr, item)) arr.splice(arr.indexOf(item), 1)
    return arr
  }

  contains (arr, item) {
    // this is black magic so it doesn't compare js references for objects
    return JSON.stringify(arr).indexOf(JSON.stringify(item)) > -1
  }

  postLinkFn (scope, elem, attrs) {
    const checklistModel = attrs.checklistModel
    const value = this.$parse(attrs.checklistValue)(scope.$parent)
    const isChecked = this.contains(this.$parse(checklistModel)(scope.$parent), value)
    // we check what's already checked and we assign the proper values
    if (isChecked) {
      attrs.$set('ngChecked', true)
      this.$parse(attrs.ngModel).assign(scope, true)
    } else {
      this.$parse(attrs.ngModel).assign(scope, false)
    }

    // avoid infinite loop before compilation
    attrs.$set('checklistModel', null)
    // compile with `ng-model` pointing to `checked`
    this.$compile(elem)(scope)
    // we put back what we removed here
    attrs.$set('checklistModel', checklistModel)

    // watch changes and assign proper values
    scope.$watch(attrs.ngModel, (newValue, oldValue) => {
      if (newValue === oldValue) return
      let current = this.$parse(checklistModel)(scope.$parent)

      if (newValue) {
        this.$parse(checklistModel).assign(scope.$parent, this.add(current, value))
      } else {
        this.$parse(checklistModel).assign(scope.$parent, this.remove(current, value))
      }
    })
  }
}
