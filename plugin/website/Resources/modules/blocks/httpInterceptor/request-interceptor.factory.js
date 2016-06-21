import HttpInterceptor from './http-interceptor'

let _$q = new WeakMap()
let _requestHandler = new WeakMap()
let _$injector = new WeakMap()
let _http = new WeakMap()

export default class RequestInterceptor extends HttpInterceptor {
  constructor ($q, $injector, requestHandler) {
    super()
    _$q.set(this, $q)
    _requestHandler.set(this, requestHandler)
    _$injector.set(this, $injector)
  }

  request (config) {
    // Start request loading
    _requestHandler.get(this).requestStarted()

    return config
  }

  requestError (rejection) {
    // End request loading
    if (!_http.get(this)) {
      _http.set(this, _$injector.get(this).get('$http'))
    }

    if (_http.get(this).pendingRequests.length < 1) {
      _requestHandler.get(this).requestEnded()
    }
    // Show globar error message
    _requestHandler.get(this).requestError(rejection)

    return _$q.get(this).reject(rejection)
  }

  response (response) {
    // End request loading
    if (!_http.get(this)) {
      _http.set(this, _$injector.get(this).get('$http'))
    }

    if (_http.get(this).pendingRequests.length < 1) {
      _requestHandler.get(this).requestEnded()
    }
    // Show global success message
    _requestHandler.get(this).requestSuccess(response)

    return response || _$q.get(this).when(response)
  }

  responseError (rejection) {
    // End request loading
    if (!_http.get(this)) {
      _http.set(this, _$injector.get(this).get('$http'))
    }

    if (_http.get(this).pendingRequests.length < 1) {
      _requestHandler.get(this).requestEnded()
    }
    // Show global error message
    _requestHandler.get(this).requestError(rejection)

    return _$q.get(this).reject(rejection)
  }
}

HttpInterceptor.$inject = [ '$q', '$injector', 'requestHandler' ]