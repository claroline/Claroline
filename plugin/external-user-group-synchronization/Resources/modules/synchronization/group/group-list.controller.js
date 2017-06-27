/**
 * Created by panos on 5/30/17.
 */
export class GroupListController {
  constructor(GroupListService) {
    this._GroupListService = GroupListService
    this.initialized = false
  }

  $onInit() {
    this.groups = []
    this.totalGroups = 0
    this.configured = true
    this.fieldNames = [ 'name', 'user_count' ]
    this.actions = [{'name': 'synchronize', 'icon': 'fa-refresh', 'action': this.synchronizeGroup.bind(this)}]
    this.getGroups()
    this.hasGroupConfig()
  }

  hasGroupConfig() {
    this._GroupListService.hasGroupConfig().then(data => {
      this.configured = data
    })
  }

  synchronizeGroup(group) {
    this._GroupListService.synchronizeGroup(group.id, true).then(data => {
      group['user_count'] = data.userCount
      this.onAlert({'$alert': {'type' : 'success', 'msg' : 'group_sync_success'}})
    }, () => {
      this.onAlert({'$alert': {'type' : 'danger', 'msg' : 'group_sync_error'}})
    })
  }

  getGroups(search) {
    search = search || {}
    this._GroupListService.getGroups(search).then(data => {
      this.totalGroups = data.totalItems
      this.groups = data.items
    }, () => {
      this.onAlert({'$alert': {'type' : 'danger', 'msg' : 'group_list_load_error'}})
    }).finally(() => { this.initialized = true })
  }

}

GroupListController.$inject = [ 'GroupListService' ]