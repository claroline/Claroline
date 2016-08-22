// heavily inspired from https://github.com/vitalets/checklist-model/blob/master/checklist-model.js for learning purpose
export default class StickBottomDirective {
  constructor ($parse, $window, $timeout) {
    this.$parse = $parse
    this.$window = $window
    this.$timeout = $timeout
    this.restrict = 'A'
  }

  link (scope, $el, $attrs) {
    const options = this.$parse($attrs.scrollBottom)(scope.$parent)
    const always = options.always ? true : false
    const el = $el[0]

    scope.$watch(
      // I should watch the height instead but I couldn't make it work (yet)
      () => {
        return $el.html()},
      () => {
        this.scrollIfReady(el, always)
      },
      true
    )

    this.$timeout(this.scrollBottom(el), 0, false)
  }

  scrollBottom (el) {
    el.scrollTop = el.scrollHeight
  }

  isBottom (el) {
    return el.scrollTop + el.clientHeight + 1 >= el.scrollHeight
  }

  scrollReady (el, always) {
    const ready = this.isBottom(el) || always
    return ready
  }

  scrollIfReady (el, always) {
    if (this.scrollReady(el, always)) this.scrollBottom(el)
  }

}
