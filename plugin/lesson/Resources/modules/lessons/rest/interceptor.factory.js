let _$location = new WeakMap()
let _Alerts = new WeakMap()
let _transFilter = new WeakMap()

export default class InterceptorFactory {
  constructor($location, Alerts, transFilter) {
    _$location.set(this, $location)
    _Alerts.set(this, Alerts)
    _transFilter.set(this, transFilter)
  }

  get responseError() {
    return rejection => {
      if (rejection.status === 401 || rejection.status === 500) {
        _Alerts.get(this).push({
          'type': 'danger',
          'msg': _transFilter.get(this)('error_' + rejection.status, {}, 'icap_lesson')})
      }
      if (rejection.status === 404) {
        _$location.get(this).path('/error/404')
      }
    }
  }
}

InterceptorFactory.$inject = [
  'transFilter'
]