var controller = function(
    $http,
    clarolineSearch,
    clarolineAPI
) {
    console.log('Init user controller');

    var vm = this;
    var translator = window.Translator;

    var translate = function(key) {
        return translator.trans(key, {}, 'platform');
    }

    var generateQsForSelected = function() {
        var qs = '';

        for (var i = 0; i < this.selected.length; i++) {
            qs += 'userIds[]=' + this.selected[i].id + '&';
        }

        return qs;

    }.bind(this);

    var deleteCallback = function(data) {
        clarolineAPI.removeElements(this.selected, this.users);
        this.selected.splice(0, this.selected.length);
        this.alerts.push({
            type: 'success',
            msg: translate('user_removed_success_message')
        });0
    }.bind(this);

    var initPwdCallback = function(data) {
        alert('yeah !!');
    }

    this.userActions = [];

    $http.get(Routing.generate('api_get_user_admin_actions')).then(function(d) {
        vm.userActions = d.data;
    }.bind(this));

    this.search = '';
    this.savedSearch = [];
    this.users = [];
    this.selected = [];
    this.alerts = [];
    this.fields = [];

    $http.get(Routing.generate('api_get_user_searchable_fields')).then(function(d) {
        vm.fields = d.data;
    })

    var columns = [
        {name: translate('username'), prop: "username", isCheckboxColumn: true, headerCheckbox: true},
        {name: translate('first_name'), prop: "firstName"},
        {name: translate('last_name'), prop:"lastName"},
        {name: translate('email'), prop: "mail"},
        {
            name: translate('actions'),
            cellRenderer: function(scope) {
                var tr = translate('show_as');
                var content = "<a class='btn btn-default' href='" + Routing.generate('claro_desktop_open', {'_switch': scope.$row.username}) + "' data-toggle='tooltip' data-placement='bottom' title='' data-original-title='" + tr + "' role='button'>" +
                    "<i class='fa fa-eye'></i>" +
                    "</a>";

                for (var i = 0; i < vm.userActions.length; i++) {
                    var route = Routing.generate('admin_user_action', {
                        'user': scope.$row.id,
                        'action': vm.userActions[i]['tool_name']
                    });
                    content += "<a class='btn btn-default' href='" + route + "'><i class='fa " + vm.userActions[i].class + "'></i></a>";
                }

                return '<div>' + content + '</div>';
            }
        }
    ];

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
        }
    };

    this.onSearch = function(searches) {
        this.savedSearch = searches;
        clarolineSearch.find('api_get_search_users', searches, this.dataTableOptions.paging.offset, this.dataTableOptions.paging.size).then(function(d) {
            this.users = d.data.users;
            this.dataTableOptions.paging.count = d.data.total;
        }.bind(this));
    }.bind(this);

    this.paging = function(offset, size) {
        clarolineSearch.find('api_get_search_users', this.savedSearch, offset, size).then(function(d) {
            var users = d.data.users;

            //I know it's terrible... but I have no other choice with this table.
            for (var i = 0; i < offset * size; i++) {
                users.unshift({});
            }

            this.users = users;
            this.dataTableOptions.paging.count = d.data.total;
        }.bind(this));
    }.bind(this);

    this.clickDelete = function() {
        var url = Routing.generate('api_delete_users') + '?' + generateQsForSelected();

        clarolineAPI.confirm(
            {url: url, method: 'DELETE'},
            deleteCallback,
            translate('delete_users'),
            translate('delete_users_confirm')
        );
    };

    this.initPassword = function() {
        var url = Routing.generate('api_users_password_initialize') + '?' + generateQsForSelected();

        clarolineAPI.confirm(
            {url: url, method: 'GET'},
            initPwdCallback,
            translate('init_password'),
            translate('init_password_confirm')
        );
    }

    this.addAlert = function() {
        this.alerts.push({msg: 'Another alert!'});
    }.bind(this);

    this.closeAlert = function(index) {
        this.alerts.splice(index, 1);
    }.bind(this);
};

angular.module('UsersManager').controller('UserController', [
    '$http',
    'clarolineSearch',
    'clarolineAPI', controller
]);