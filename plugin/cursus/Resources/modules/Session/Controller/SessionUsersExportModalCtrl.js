/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class SessionUsersExportModalCtrl {
  constructor($uibModalInstance, SessionService, sessionId) {
    this.$uibModalInstance = $uibModalInstance
    this.SessionService = SessionService
    this.sessionId = sessionId
    this.learners = true
    this.trainers = false
  }

  submit() {
    let exportType = 0

    if (this.learners) {
      exportType += 1
    }
    if (this.trainers) {
      exportType += 2
    }
    this.SessionService.exportUsers(this.sessionId, exportType)
    this.$uibModalInstance.close()
  }
}
