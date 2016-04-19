/**
 * Created by panos on 3/31/16.
 */
import webtreeNodeTemplate from './webtree-node.partial.html'
import webtreeNodeController from './webtree-node.controller'

let _contents = new WeakMap()
let _compiledContents = new WeakMap()
let _$compile = new WeakMap()

export default class WebtreeNodeDirective {
  constructor ($compile) {
    _$compile.set(this, $compile)
    this.restrict = 'EA'
    this.scope = {
      node: '=ngModel',
      depth: '@'
    }
    this.require = ['^webtree', '?^^webtreeNode', 'webtreeNode']
    this.template = webtreeNodeTemplate
    this.controller = webtreeNodeController
    this.controllerAs = 'vm'
    this.bindToController = true
    this.compile = this._compile.bind(this)
  }

  _compile (tElement) {
    _contents.set(this, tElement.contents().remove())
    _compiledContents.set(this, null)

    return {
      pre: this._preLink.bind(this)
    }
  }

  _preLink($scope, element, attr, ctrl) {
    // Pass parent controller to node controller
    ctrl[2].webtree = ctrl[0]
    if (ctrl[1] == null) {
      ctrl[2].node.parent = ctrl[0].root
    } else {
      ctrl[2].node.parent = ctrl[1].node
    }
    if (!_compiledContents.get(this)) {
      _compiledContents.set(this, _$compile.get(this)(_contents.get(this)))
    }

    _compiledContents.get(this)($scope, (clone) => { element.append(clone) })
  }
}

WebtreeNodeDirective.$inject = [ "$compile" ]