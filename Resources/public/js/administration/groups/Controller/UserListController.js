var controller = function($http, clarolineSearch, $stateParams, GroupAPI) {

    var translate = function(key) {
        return translator.trans(key, {}, 'platform');
    }

    var mergeSearches = function(searches, element) {
        var found = false;

        for (var i = 0; i < searches.length; i++) {
            if (searches[i] == element) found = true;
        }

        if (!found) {
            searches.push(element);
        }

        return searches;
    }   

    var vm = this;
    this.groupId = $stateParams.groupId;
    this.group = [];
    this.users = [];
    this.search = '';
    this.savedSearch = [];
    this.selected = [];
    this.alerts = [];
    this.fields = [];
    var baseSearch = {'field': 'group', 'id': 0, 'value': this.groupId};

    GroupAPI.find(this.groupId).then(function(d) {
        console.log(d);
        this.group = d.data;
    }.bind(this));

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
        //removing group from fields
        for (var i = 0; i < vm.fields.length; i++) {
            if (vm.fields[i] === 'group') {
                 vm.fields.splice(i, 1);
            }
        }
    });

    this.onSearch = function(searches) {
        searches = mergeSearches(searches, baseSearch);
        this.savedSearch = searches;
        clarolineSearch.find('api_get_search_users', searches, this.dataTableOptions.paging.offset, this.dataTableOptions.paging.size).then(function(d) {
            this.users = d.data.users;
            this.dataTableOptions.paging.count = d.data.total;
        }.bind(this));
    }.bind(this);

    this.paging = function(offset, size) {
        this.savedSearch = mergeSearches(this.savedSearch, baseSearch);
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
};

angular.module('GroupsManager').controller('UserListController', [
    '$http',
    'clarolineSearch',
    '$stateParams',
    'GroupAPI',
    controller
]);