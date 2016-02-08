var controller = function($http, clarolineSearch, $stateParams) {

    var translate = function(key) {
        return translator.trans(key, {}, 'platform');
    }

    this.groupId = $stateParams.groupId;
    this.users = [];
    this.search = '';
    this.savedSearch = [];
    this.selected = [];
    this.alerts = [];
    this.fields = [];

    var columns = [
        {name: translate('username'), prop: "username", isCheckboxColumn: true, headerCheckbox: true},
        {name: translate('first_name'), prop: "firstName"},
        {name: translate('last_name'), prop:"lastName"},
        {name: translate('email'), prop: "mail"}
    ];

    this.dataTableOptions = {
        scrollbarV: false,
        columnMode: 'force',
        headerHeight: 50,
        footerHeight: 50,
        //selectable: true,
        multiSelect: true,
        checkboxSelection: true,
        columns: columns,
        paging: {
            externalPaging: true,
            size: 10
        }
    };

    $http.get(Routing.generate('api_get_user_searchable_fields')).then(function(d) {
        vm.fields = d.data;
        console.log(fields);
    })

    clarolineSearch.find('api_get_search_users', searches, this.dataTableOptions.paging.offset, this.dataTableOptions.paging.size).then(function(d) {
        this.users = d.data.users;
        this.dataTableOptions.paging.count = d.data.total;
    }.bind(this));
};

angular.module('GroupsManager').controller('UserListController', [
    '$http',
    'clarolineSearch',
    '$stateParams',
    controller
]);