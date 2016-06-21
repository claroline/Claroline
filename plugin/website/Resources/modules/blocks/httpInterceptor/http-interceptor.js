/**
 * Created by panos on 4/6/16.
 */
export default class HttpInterceptor {
  constructor() {
    ['request', 'requestError', 'response', 'responseError']
      .forEach((method) => {
        if(this[method]) {
          this[method] = this[method].bind(this)
        }
      })
  }
}