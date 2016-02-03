var controller = function($http, clarolineSearch) {
    var translate = function(key) {
        return translator.trans(key, {}, 'platform');
    }

    this.search = '';
    this.savedSearch = [];
    this.fields = [];

    var vm = this;

    $http.get(Routing.generate('api_get_group_searchable_fields')).then(function(d) {
        vm.fields = d.data;
    });

    var columns = [
        {name: translate('name'), prop: "name", isCheckboxColumn: true, headerCheckbox: true},
        {
            name: translate('actions'),
            cellRenderer: function(scope) {
                var groupId = scope.$row.id;
                var actions = '<a ui-sref="administration.groups.users({groupId: ' + groupId + '})"> users </a>';
                //var actions = '<a href="#"> sdfsdf </a>';
                return actions;
            }
        }
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

        this.dataTableOptions.paging.count = 2;

    this.onSearch = function(searches) {
        this.savedSearch = searches;
        clarolineSearch.find('api_get_search_groups', searches, this.dataTableOptions.paging.offset, this.dataTableOptions.paging.size).then(function(d) {
            this.groups = d.data.groups;
            this.dataTableOptions.paging.count = d.data.total;
        }.bind(this));
    }.bind(this);

    this.paging = function(offset, size) {
        clarolineSearch.find('api_get_search_groups', this.savedSearch, offset, size).then(function(d) {
            var groups = d.data.groups;

            //I know it's terrible... but I have no other choice with this table.
            for (var i = 0; i < offset * size; i++) {
                groups.unshift({});
            }

            this.groups = groups;
            this.dataTableOptions.paging.count = d.data.total;
        }.bind(this));
    }.bind(this);
};

angular.module('GroupsManager').controller('GroupController', [
    '$http',
    'clarolineSearch',
    controller
]);
