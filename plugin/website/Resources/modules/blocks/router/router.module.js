import register from '../../utils/register'
import routeHelperConfig from './route-helper.provider'
import routeHelper from './route-helper.factory'

let registerApp = new register('blocks.router', [
  'ngRoute'
])
registerApp
  .provider('routeHelperConfig', routeHelperConfig)
  .factory('routeHelper', routeHelper)
