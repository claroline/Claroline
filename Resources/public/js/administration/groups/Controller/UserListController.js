var controller = function($http, clarolineSearch, $stateParams, GroupAPI, clarolineAPI) {

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

    var addToGroupCallback = function(data) {
        this.users = this.users.concat(data);

        for (var i = 0; i < data.length; i++) {
            this.alerts.push({
                type: 'success',
                msg: translate('user_added', {'%username%': data[i].username})
            });
        }

        this.dataTableOptions.paging.count += data.length;
    }.bind(this);

    var removeFromGroupCallback = function(data) {
        clarolineAPI.removeElements(this.selected, this.users);
        this.dataTableOptions.paging.count -= this.selected.length;

        for (var i = 0; i < this.selected.length; i++) {
            console.log(this.selected[i]);
            this.alerts.push({
                type: 'success',
                msg: translate('user_removed', {'%username%': this.selected[i].username})
            });
        }

        this.selected.splice(0, this.selected.length);
    }.bind(this);

    var pickerCallback = function(data) {
        var userIds = [];
        var li = '';

        for (var i = 0; i < data.length; i++) {
            userIds.push(data[i]);
            li += '<li>' + data.username + '</li>';
        }

        var url = Routing.generate('api_add_users_to_group', {'group': this.groupId}) + '?' + clarolineAPI.generateQueryString(userIds, 'userIds');
        var userList = "<ul>" + li + "</ul>";
        console.log(this.group);

        clarolineAPI.confirm(
            {url: url, method: 'GET'},
            addToGroupCallback,
            translate('add_users_to_group', {group: this.group.name, user_list: userList}),
            translate('add_users_to_group_confirm')
        );

    }.bind(this);

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
        this.group = d.data;
    }.bind(this));

    var columns = [
        {name: translate('username'), prop: "username", isCheckboxColumn: true, headerCheckbox: true},
        {name: translate('first_name'), prop: "firstName"},
        {name: translate('last_name'), prop:"lastName"},
        {name: translate('email'), prop: "mail"}
    ];

    this.dataTableOptions = {
        scrollbarV: true,
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

    this.addUser = function() {
        //maybe improve this later...
        var excludeUser = [];
        var userPicker = new UserPicker();
        var options = {
            'picker_name': 'user_group_picker',
            'picker_title': translate('add_user'),
            'multiple': true,
            'blacklist': excludeUser
        }

        userPicker.configure(options, pickerCallback);
        userPicker.open();
    }.bind(this);

    this.clickDelete = function() {
        var url = Routing.generate('api_remove_users_from_group',  {'group': this.groupId}) + '?' + clarolineAPI.generateQueryString(this.selected, 'userIds');

        clarolineAPI.confirm(
            {url: url, method: 'GET'},
            removeFromGroupCallback,
            translate('remove_users_from_group'),
            translate('remove_users_from_group_confirm')
        );
    }.bind(this);

    this.closeAlert = function(index) {
        this.alerts.splice(index, 1);
    }.bind(this);
};

angular.module('GroupsManager').controller('UserListController', [
    '$http',
    'clarolineSearch',
    '$stateParams',
    'GroupAPI',
    'clarolineAPI',
    controller
]);