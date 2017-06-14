/**
 * Created by panos on 6/7/17.
 */
export class UserSyncModalController {
  constructor($uibModalInstance, roles) {
    this.roles = roles
    this._$uibModalInstance = $uibModalInstance
    this.cas = 'true'
    this.role = this.roles[0]
  }

  ok() {
    this._$uibModalInstance.close({'role':this.role, 'cas':this.cas})
  }

  cancel() {
    this._$uibModalInstance.dismiss('cancel')
  }
}

UserSyncModalController.$inject = [ '$uibModalInstance', 'roles' ]