/**
 * Created by panos on 3/31/16.
 */
import webtreeController from './webtree.controller'
import webtreeTemplate from './webtree.partial.html'

export default class WebtreeDirective {
  constructor () {
    this.restrict = 'EA'
    this.scope = {
      tree: '=',
      instance: '='
    }
    this.template = webtreeTemplate
    this.controller = webtreeController
    this.controllerAs = 'vm'
    this.bindToController = true
  }
}