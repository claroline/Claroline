import CreateGroupModalController from './CreateGroupModalController'
import EditGroupModalController from './EditGroupModalController'

export default class GroupController {
  constructor ($http, ClarolineSearchService, ClarolineAPIService, $uibModal) {
    this.$http = $http
    this.ClarolineSearchService = ClarolineSearchService
    this.ClarolineAPIService = ClarolineAPIService
    this.$uibModal = $uibModal
    this.search = ''
    this.savedSearch = []
    this.fields = []
    this.selected = []
    this.alerts = []
    this.groups = undefined
    this.groupActions = []

    const columns = [
      {name: this.translate('name'), prop: 'name', isCheckboxColumn: true, headerCheckbox: true},
      {
        name: this.translate('actions'),
        cellRenderer: (scope) => {
          const groupId = scope.$row.id
          let content = '<a class="btn btn-default pointer" ui-sref="users.groups.users({groupId: ' + groupId + '})"><i class="fa fa-users"></i> </a>'
          content += '<a class="btn btn-default pointer" ng-click="gc.clickEdit($row)"><i class="fa fa-cog"></i></a>'

          content = this.groupActions.reduce((content, action) => {
            const route = Routing.generate('admin_group_action', {
              'group': scope.$row.id,
              'action': action['id']
            })
            return content + `<a class='btn btn-default' href='${route}'><i class='fa ${action.class}'></i></a>`
          }, content)

          return content
        }
      }
    ]

    this.dataTableOptions = {
      scrollbarV: false,
      columnMode: 'force',
      headerHeight: 50,
      footerHeight: 50,
      selectable: true,
      multiSelect: true,
      checkboxSelection: true,
      columns: columns,
      paging: {
        externalPaging: true,
        size: 10
      },
      sizes: [10, 20, 50]
    }

    $http.get(Routing.generate('api_get_group_searchable_fields'))
      .then(d => this.fields = d.data)

    $http.get(Routing.generate('api_get_group_admin_actions'))
      .then(d => this.groupActions = d.data)

    this._deleteCallback = this._deleteCallback.bind(this)
    this._onSearch = this._onSearch.bind(this)
  }

  translate (key, data = {}) {
    return window.Translator.trans(key, data, 'platform')
  }

  generateQsForSelected () {
    let qs = ''

    for (let i = 0; i < this.selected.length; i++) {
      qs += 'groupIds[]=' + this.selected[i].id + '&'
    }

    return qs
  }

  closeAlert (index) {
    this.alerts.splice(index, 1)
  }

  clickNew () {
    const modalInstance = this.$uibModal.open({
      templateUrl: Routing.generate('api_get_group_create_form', {'_format': 'html'}),
      controller: 'CreateGroupModalController',
      controllerAs: 'cgfm'
    })

    modalInstance.result.then(result => {
      if (!result) return
      this.groups.push(result)
      this.dataTableOptions.paging.count = this.groups.length

      this.alerts.push({
        type: 'success',
        msg: this.translate('group_created', {group: result.name})
      })
    })
  }

  clickEdit (group) {
    const modalInstance = this.$uibModal.open({
      templateUrl: Routing.generate('api_get_group_edit_form', {'_format': 'html', 'group': group.id}) + '?bust=' + Math.random().toString(36).slice(2),
      controller: 'EditGroupModalController',
      controllerAs: 'egfm'
    })

    modalInstance.result.then(result => {
      if (!result) return
      this.groups = this.ClarolineAPIService.replaceById(result, this.groups)
    })
  }

  paging (offset, size) {
    this.ClarolineSearchService.find('api_get_search_groups', this.savedSearch, offset, size).then(d => {
      const groups = d.data.groups

      // I know it's terrible... but I have no other choice with this table.
      for (let i = 0; i < offset * size; i++) {
        groups.unshift({})
      }

      this.groups = groups
      this.dataTableOptions.paging.count = d.data.total
    })
  }

  clickDelete () {
    const url = Routing.generate('api_delete_groups') + '?' + this.generateQsForSelected()

    let groups = ''

    for (let i = 0; i < this.selected.length; i++) {
      groups += this.selected[i].name
      if (i < this.selected.length - 1) groups += ', '
    }

    this.ClarolineAPIService.confirm(
      {url: url, method: 'DELETE'},
      this._deleteCallback,
      this.translate('delete_groups'),
      this.translate('delete_groups_confirm', {group_list: groups})
    )
  }

  _onSearch (searches) {
    this.savedSearch = searches
    this.ClarolineSearchService.find('api_get_search_groups', searches, 0, this.dataTableOptions.paging.size).then(d => {
      this.groups = d.data.groups
      this.dataTableOptions.paging.count = d.data.total
      this.dataTableOptions.paging.offset = 0
    })
  }

  _deleteCallback (data) {
    for (let i = 0; i < this.selected.length; i++) {
      this.alerts.push({
        type: 'success',
        msg: this.translate('group_removed', {group: this.selected[i].name})
      })
    }

    this.dataTableOptions.paging.count -= this.selected.length
    this.ClarolineAPIService.removeElements(this.selected, this.groups)
    this.selected.splice(0, this.selected.length)
  }
}
