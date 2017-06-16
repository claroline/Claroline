import subMenuTemplate from './flexnav-submenu.partial.html'
import subMenuController from './flexnav-submenu.controller'
let _$compile = new WeakMap()
let _contents = new WeakMap()
let _compiledContents = new WeakMap()

export default class uiFlexnavSubmenuDirective {
  constructor($compile) {
    _$compile.set(this, $compile)

    this.scope = {
      menu: '=',
      level: '@'
    }
    this.template = subMenuTemplate
    this.require = ['^uiFlexnav', '?^^uiFlexnavSubmenu', 'uiFlexnavSubmenu']
    this.restrict = 'EA'
    this.replace = true
    this.controller = subMenuController
    this.controllerAs = 'vm'
    this.bindToController = true
    this.compile = this._compile.bind(this)
  }

  _compile(tElement) {
    _contents.set(this, tElement.contents().remove())
    _compiledContents.set(this, null)

    return {
      pre: this._preLink.bind(this)
    }
  }

  _preLink($scope, element, attr, ctrl) {
    ctrl[2].flexnav = ctrl[0]
    ctrl[2].parent = ctrl[1] || ctrl[0]

    if (!_compiledContents.get(this)) {
      _compiledContents.set(this, _$compile.get(this)(_contents.get(this)))
    }

    _compiledContents.get(this)($scope, (clone) => { element.append(clone) })
  }
}

uiFlexnavSubmenuDirective.$inject = [ '$compile' ]
