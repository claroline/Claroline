let _$location = new WeakMap()
let _Alerts = new WeakMap()

export default class InterceptorFactory {
  constructor($location, Alerts) {
    _$location.set(this, $location)
    _Alerts.set(this, Alerts)
  }

  get responseError() {
    return rejection => {
      if (rejection.status === 401 || rejection.status === 500) {
        _Alerts.get(this).push({
          "type": 'danger',
          "msg": Translator.trans('error_' + rejection.status, 'icap_lesson')})
      }
      if (rejection.status === 404) {
        _$location.get(this).path('/error/404')
      }
    }
  }
}