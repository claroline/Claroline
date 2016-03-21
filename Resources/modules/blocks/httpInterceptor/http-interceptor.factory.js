HttpInterceptor.construct.$inject = [ '$q', '$injector', 'requestHandler' ]

export default class HttpInterceptor {
  construct ($q, $injector, requestHandler) {
    this.$q = $q
    this.$injector = $injector
    this.requestHandler = requestHandler
    this.$http = this.$http || $injector.get('$http')
  }

  request (config) {
    // Start request loading
    this.requestHandler.requestStarted()

    return config
  }

  requestError (rejection) {
    // End request loading
    if (this.$http.pendingRequests.length < 1) {
      this.requestHandler.requestEnded()
    }
    // Show globar error message
    this.requestHandler.requestError(rejection)

    return this.$q.reject(rejection)
  }

  response (response) {
    // End request loading
    if (this.$http.pendingRequests.length < 1) {
      this.requestHandler.requestEnded()
    }
    // Show global success message
    this.requestHandler.requestSuccess(response)

    return response || this.$q.when(response)
  }

  responseError (rejection) {
    // End request loading
    if (this.$http.pendingRequests.length < 1) {
      this.requestHandler.requestEnded()
    }
    // Show global error message
    this.requestHandler.requestError(rejection)

    return this.$q.reject(rejection)
  }

}