/**
 * Alert Service
 * Manage stack of Alerts
 */
export default class AlertService {
  constructor($timeout) {
    this.$timeout = $timeout

    this.duration = 7000
    this.alerts = []
    this.current = {}
  }

  getCurrent() {
    if (this.alerts.length > 0) {
      this.displayAlert(this.alerts.shift())
    }

    return this.current
  }

  /**
   * Get all alerts.
   *
   * @returns {Array}
   */
  getAlerts() {
    return this.alerts
  }

  /**
   * Add a new alert.
   *
   * @param msg
   * @param type
   *
   * @returns AlertService
   */
  addAlert(type, msg, timeoutFlag) {
    let display = false
    if (this.alerts.length === 0) {
      display = true
    }

      // Store alert
    this.alerts.push({ type: type, msg: msg })

    if (display) {
      if (timeoutFlag) {
        this.displayAlert(this.alerts.shift())
      } else {
        this.displayAlertWithoutTimeout(this.alerts.shift())
      }
    }
  }

  displayAlertWithoutTimeout(alert) {
    this.current.type = alert.type
    this.current.msg = alert.msg
  }

  displayAlert(alert) {
    this.current.type = alert.type
    this.current.msg = alert.msg

      // Auto close alert
    this.$timeout(() => this.closeCurrent(), this.duration)
  }

  /**
   * Close current displayed alert.
   */
  closeCurrent() {
    if (this.current) {
          // Empty the current alert object
      delete this.current.type
      delete this.current.msg
    }

    if (this.alerts.length > 0) {
          // Display next alert in the stack
      this.displayAlert(this.alerts.shift())
    }
  }
}
