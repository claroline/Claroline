/**
 * Created by panos on 4/13/16.
 */
let _$rootScope = new WeakMap()

export default class alert {
  constructor ($rootScope) {
    _$rootScope.set(this, $rootScope)
  }

  new (obj) {
    obj = Object.assign(this._emptyAlert(), obj)
    obj.close = this.close.bind(this)
    let $rootScope = _$rootScope.get(this)
    if (!$rootScope.alerts) {
      $rootScope.alerts = []
    }
    $rootScope.alerts.push(obj)
  }

  close (index) {
    _$rootScope.get(this).alerts.splice(index, 1)
  }

  _emptyAlert () {
    return {
      title: '',
      content: '',
      duration: 3000,
      type: 'info'
    }
  }
}

alert.$inject = ['$rootScope']