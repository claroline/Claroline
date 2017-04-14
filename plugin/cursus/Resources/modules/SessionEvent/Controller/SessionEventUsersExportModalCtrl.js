/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class SessionEvantUsersExportModalCtrl {
  constructor($uibModalInstance, SessionEventService, sessionEventId) {
    this.$uibModalInstance = $uibModalInstance
    this.SessionEventService = SessionEventService
    this.sessionEventId = sessionEventId
    this.participants = true
    this.trainers = false
  }

  submit() {
    let exportType = 0

    if (this.participants) {
      exportType += 1
    }
    if (this.trainers) {
      exportType += 2
    }
    this.SessionEventService.exportUsers(this.sessionEventId, exportType)
    this.$uibModalInstance.close()
  }
}
