import register from '../../utils/register'
import requestInterceptorFactory from './request-interceptor.factory'
import requestHandlerFactory from './request-handler.factory'
import alertFactory from './alert.factory'

let registerApp = new register('blocks.httpInterceptor', [])
registerApp
  .factory('requestHandler', requestHandlerFactory)
  .factory('httpInterceptor', requestInterceptorFactory)
  .factory('$alert', alertFactory)
  .config(['$httpProvider', ($httpProvider) => { $httpProvider.interceptors.push('httpInterceptor') }])
