import angular from 'angular/index'
import httpInterceptorConfig from './http-interceptor.config'
import httpInterceptorFactory from './http-interceptor.factory'
import requestHandlerFactory from './request-handler.factory'

angular.module('blocks.httpInterceptor', [])
  .config(httpInterceptorConfig)
  .factory('httpInterceptor', httpInterceptorFactory)
  .factory('requestHandler', requestHandlerFactory)
