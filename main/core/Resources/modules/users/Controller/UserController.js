import RemoveByCsvModalController from './RemoveByCsvModalController'
import UserInfoModalController from './UserInfoModalController'
import UserInfoHtml from '../Partial/user_info.html'

export default class UserController {
    constructor($http, ClarolineSearchService, ClarolineAPIService, $uibModal) {
        this.ClarolineAPIService = ClarolineAPIService
        this.$http = $http
        this.ClarolineSearchService = ClarolineSearchService
        this.userActions = []
        this.search = ''
        this.savedSearch = []
        this.users = []
        this.roles = []
        this.selectedRoles = []
        this.selected = []
        this.alerts = []
        this.fields = []
        this.managedOrganizations = []
        this.$uibModal = $uibModal

        const columns = [
            {name: this.translate('username'), prop: "username", isCheckboxColumn: true, headerCheckbox: true},
            {name: this.translate('first_name'), prop: "firstName"},
            {name: this.translate('last_name'), prop:"lastName"},
            {name: this.translate('email'), prop: "mail"},
            {
                name: this.translate('actions'),
                cellRenderer: scope => {
                    let tr = this.translate('show_as');

                    let content =
                    `<a class='btn btn-default'
                        href='${Routing.generate('claro_desktop_open', {'_switch': scope.$row.username})}'
                        data-toggle='tooltip'
                        data-placement='bottom'
                        title=''
                        data-original-title='${tr}'
                        role='button'>
                        <i class='fa fa-eye'></i>
                    </a>`

                    content = this.userActions.reduce((content, action) => {
                        const route = Routing.generate('admin_user_action', {
                            'user': scope.$row.id,
                            'action': action['id']
                        });
                        return content + `<a class='btn btn-default' href='${route}'><i class='fa ${action.class}'></i></a>`
                    }, content)

                    content += `
                        <button class='btn btn-default' ng-click='uc.userInfo($row)'><i class='fa fa-info'></i></button>
                    `

                    return `<div>${content}</div>`
                }
            }
        ];

        let availableColumns = angular.copy(columns)
        //removing username from the selection
        availableColumns.splice(0, 1)

        this.dataTableOptions = {
            scrollbarV: false,
            columnMode: 'force',
            headerHeight: 50,
            footerHeight: 50,
            multiSelect: true,
            checkboxSelection: true,
            selectable: true,
            columns: columns,
            sizes: [10, 20, 50, 100],
            paging: {
                externalPaging: true,
                size: 10
            },
            availableColumns: availableColumns
        };

        $http.get(Routing.generate('api_get_user_admin_actions'))
            .then(d => this.userActions = d.data)

        $http.get(Routing.generate('api_get_user_fields'))
            .then(d => this.fields = d.data)

        $http.get(Routing.generate('api_get_platform_roles'))
            .then(d => this.roles = d.data)

        this._onSearch = this._onSearch.bind(this)
        this._initPwdCallback = this._initPwdCallback.bind(this)
        this._deleteCallback = this._deleteCallback.bind(this)
        this._updateRolesCallback = this._updateRolesCallback.bind(this)
    }

    translate(key, data = {}) {
        return window.Translator.trans(key, data, 'platform');
    }

    generateQsForSelectedUsers() {
        let qs = '';

        for (let i = 0; i < this.selected.length; i++) {
            qs += 'userIds[]=' + this.selected[i].id + '&'
        }

        return qs
    }

    generateQsForSelectedRoles() {
        let qs = '';

        for (let i = 0; i < this.selected.length; i++) {
            qs += 'roleIds[]=' + this.roles[i].id + '&'
        }

        return qs
    }

    paging(offset, size) {
        this.ClarolineSearchService.find('api_get_search_users', this.savedSearch, offset, size).then(d => {
            const users = d.data.users;

            //I know it's terrible... but I have no other choice with this table.
            for (var i = 0; i < offset * size; i++) {
                users.unshift({});
            }

            this.users = users;
            this.dataTableOptions.paging.count = d.data.total
        });
    }

    clickDelete() {
        const url = Routing.generate('api_delete_users') + '?' + this.generateQsForSelectedUsers();

        let users = ''

        for (let i = 0; i < this.selected.length; i++) {
            users +=  this.selected[i].username
            if (i < this.selected.length - 1) users += ', '
        }

        this.ClarolineAPIService.confirm(
            {url, method: 'DELETE'},
            this._deleteCallback,
            this.translate('delete_users'),
            this.translate('delete_users_confirm', {user_list: users})
        );
    }

    userInfo(user) {
        console.log(user)
        const modalInstance = this.$uibModal.open({
            template: UserInfoHtml,
            controller: UserInfoModalController,
            controllerAs: 'uimc',
            resolve: {
                user: () => {
                    return user
                }
            }
        })
    }

    initPassword() {
        const url = Routing.generate('api_users_password_initialize') + '?' + this.generateQsForSelectedUsers();

        let users = ''

        for (let i = 0; i < this.selected.length; i++) {
            users +=  this.selected[i].username
            if (i < this.selected.length - 1) users += ', '
        }

        this.ClarolineAPIService.confirm(
            {url, method: 'GET'},
            this._initPwdCallback,
            this.translate('init_password'),
            this.translate('init_password_confirm', {user_list: users})
        );
    }

    closeAlert(index) {
        this.alerts.splice(index, 1);
    }

    csvRemove() {
        const modalInstance = this.$uibModal.open({
            template: require('../Partial/csv_remove.html'),
            controller: 'RemoveByCsvModalController',
            controllerAs: 'rbcmc'
        })

        modalInstance.result.then(result => {
            this.paging(0, this.dataTableOptions.paging.size)
        })
    }

    addRolesToSelection() {
        const url = Routing.generate('api_put_users_roles') + '?'  + this.generateQsForSelectedRoles() + this.generateQsForSelectedUsers()

        const users = this.selected.map(s => s.username).join(', ')
        const roles = this.roles.map(r => this.translate(r.translation_key)).join(', ')

        this.ClarolineAPIService.confirm(
            {url, method: 'PUT'},
            this._updateRolesCallback,
            this.translate('role_update'),
            this.translate('role_update_confirm', {user_list: users, role_list: roles})
        );
    }

    importFacetsForm() {
        const modalInstance = this.$uibModal.open({
            template: require('../Partial/csv_facet.html'),
            controller: 'ImportCsvFacetsController',
            controllerAs: 'icfc'
        })

        modalInstance.result.then(result => {
            this.alerts.push({
                type: 'success',
                msg: this.translate('facet_imported')
            })
        })
    }

    _onSearch(searches) {
        this.savedSearch = searches;
        this.ClarolineSearchService.find('api_get_search_users', searches, 0, this.dataTableOptions.paging.size)
            .then(d => {
                this.users = d.data.users;
                this.dataTableOptions.paging.count = d.data.total
                this.dataTableOptions.paging.offset = 0
            }
        )
    }

    _initPwdCallback(data) {
        for (let i = 0; i < this.selected.length; i++) {
            this.alerts.push({
                type: 'success',
                msg: this.translate('password_initialized', { user: this.selected[i].username })
            })
        }
        this.selected.splice(0, this.selected.length);
    }

    _deleteCallback(data) {
        for (let i = 0; i < this.selected.length; i++) {
            this.alerts.push({
                type: 'success',
                msg: this.translate('user_removed', { user: data[i].username })
            })
        }

        this.dataTableOptions.paging.count -= this.selected.length;
        this.ClarolineAPI.removeElements(this.selected, this.users);
        this.selected.splice(0, this.selected.length);
    }

    _updateRolesCallback(data) {
        data.forEach(user => {
            this.ClarolineAPIService.replaceById(user, this.users)
        })
        this.alerts.push({
            type: 'success',
            msg: this.translate('roles_updated')
        })
    }
}
