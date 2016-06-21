/**
 * Created by panos on 4/6/16.
 */
import register from '../../utils/register'
import {} from 'angular-ui-tree'
import webtree from './webtree.directive'
import webtreeNode from './webtree-node.directive'
import webtreeService from './webtree.service'

let registerApp = new register('ui.webtree',
  [
    'ui.tree',
    'website.constants'
  ])
registerApp
  .directive('webtree', webtree)
  .directive('webtreeNode', webtreeNode)
  .service('webtreeService', webtreeService)
  .filter('trans', () => (text, domain = 'platform', vars = {}) => window.Translator.trans(text, vars, domain))