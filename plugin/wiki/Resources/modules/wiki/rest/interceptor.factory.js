let _$location = new WeakMap()
let _Messages = new WeakMap()
let _transFilter = new WeakMap()

export default class InterceptorFactory {
  constructor($location, Messages, transFilter) {
    _$location.set(this, $location)
    _Messages.set(this, Messages)
    _transFilter.set(this, transFilter)
  }

  get responseError() {
    return rejection => {

      switch (rejection.status) {
        case 401:
          this._setMessage('warning', 'error_401')
          _$location.get(this).path('/')
          break
        case 404:
          this._setMessage('warning', 'error_404')
          break
        case 500:
          this._setMessage('danger', 'error_500')
          break
      }
    }
  }

  _setMessage(type, msg) {
    _Messages.get(this).push({
      type: type,
      msg: _transFilter.get(this)(msg, {}, 'icap_wiki')
    })
  }

}
