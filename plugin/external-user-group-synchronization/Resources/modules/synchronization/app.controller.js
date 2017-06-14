/**
 * Created by panos on 5/29/17.
 */
export class SynchronizationAppController {
  constructor(externalSource) {
    this.source = externalSource
  }

  $onInit() {
    this.alerts = []
  }

  closeAlert(index) {
    this.alerts.splice(index, 1)
  }

  pushAlert(alert) {
    this.alerts.push(alert)
  }
}

SynchronizationAppController.$inject = [ 'externalSource' ]