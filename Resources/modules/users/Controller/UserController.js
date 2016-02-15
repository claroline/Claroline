export default class UserController {
    constructor($http, ClarolineSearchService, ClarolineAPIService) {
        this.ClarolineAPIService = ClarolineAPIService
        this.$http = $http
        this.ClarolineSearchService = ClarolineSearchService
        this.userActions = []
        this.search = ''
        this.savedSearch = []
        this.users = []
        this.selected = []
        this.alerts = []
        this.fields = []

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
                            'action': action['tool_name']
                        });
                        return content + `<a class='btn btn-default' href='${route}'><i class='fa ${action.class}'></i></a>`
                    }, content)

                    return `<div>${content}</div>`
                }
            }
        ];

        this.dataTableOptions = {
            scrollbarV: false,
            columnMode: 'force',
            headerHeight: 50,
            footerHeight: 50,
            multiSelect: true,
            checkboxSelection: true,
            selectable: true,
            columns: columns,
            paging: {
                externalPaging: true,
                size: 10
            }
        };

        $http.get(Routing.generate('api_get_user_admin_actions'))
            .then(d => this.userActions = d.data)

        $http.get(Routing.generate('api_get_user_searchable_fields'))
            .then(d => this.fields = d.data)


        /**********************************************************/
        /* THE FOLLOWING CALLBACKS NEED THE CURRENT OBJECT "THIS" */
        /**********************************************************/

        //Note: it may be possible to do something else but no idea how

        //callbacks want to use the "this" from our current object. Not from the ModalController
        this._initPwdCallback = function(data) {
            for (let i = 0; i < this.selected.length; i++) {
                this.alerts.push({
                    type: 'success',
                    msg: this.translate('password_initialized', { user: this.selected[i].username })
                })
            }
            this.selected.splice(0, this.selected.length);
        }.bind(this)

        //same as above. That one we want to us this.ClarolineAPI (it's either that or inject it dynamically into the ModalController)
        this._deleteCallback = function(data) {
            for (let i = 0; i < this.selected.length; i++) {
                this.alerts.push({
                    type: 'success',
                    msg: this.translate('user_removed', { user: data[i].username })
                })
            }

            this.dataTableOptions.paging.count -= this.selected.length;
            this.ClarolineAPI.removeElements(this.selected, this.users);
            this.selected.splice(0, this.selected.length);
        }.bind(this)

        this._onSearch = function(searches) {
            this.savedSearch = searches;
            this.ClarolineSearchService.find('api_get_search_users', searches, 0, this.dataTableOptions.paging.size)
                .then(d => {
                    this.users = d.data.users;
                    this.dataTableOptions.paging.count = d.data.total
                }
            )
        }.bind(this)
    }

    translate(key, data = {}) {
        return window.Translator.trans(key, data, 'platform');
    }

    generateQsForSelected() {
        let qs = '';

        for (let i = 0; i < this.selected.length; i++) {
            qs += 'userIds[]=' + this.selected[i].id + '&'
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
        const url = Routing.generate('api_delete_users') + '?' + this.generateQsForSelected();

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

    initPassword() {
        const url = Routing.generate('api_users_password_initialize') + '?' + this.generateQsForSelected();

        let users = '';

        for (let i = 0; i < this.selected.length; i++) {
            users +=  this.selected[i].username
            if (i < this.selected.length - 1) users += ', ';
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
}