let _$rootScope = new WeakMap()
let _restService = new WeakMap()

export default class MainController {
  constructor($rootScope, lessonData, restService, $q, Alerts) {
    _$rootScope.set(this, $rootScope)
    _restService.set(this, restService)

    this.lessonData = lessonData
    this.alerts = Alerts
  }

  closeAlert(index) {
    this.alerts.splice(index, 1);
  }

}

MainController.$inject = [
  '$rootScope',
  'lesson.data',
  'restService',
  '$q',
  'Alerts'
]