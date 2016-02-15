import CreateGroupModalController from './CreateGroupModalController'
import EditGroupModalController from './EditGroupModalController'

export default class GroupController {
    constructor($http, ClarolineSearchService, ClarolineAPIService, $uibModal) {
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

        const columns = [
            {name: this.translate('name'), prop: "name", isCheckboxColumn: true, headerCheckbox: true},
            {
                name: this.translate('actions'),
                cellRenderer: function(scope) {
                    //commented code doesn't work. Idk why.
                    /*
                    const groupId = scope.$row.id;
                    const actions = 
                        `<a ui-sref="users.groups.users({groupId: '${groupId}')"><i class="fa fa-users"></i> </a>
                         <a class="pointer" ng-click="gc.clickEdit($row)"><i class="fa fa-cog"></i></a>`

                    return actions;*/

                    var groupId = scope.$row.id;
                    var users = '<a ui-sref="users.groups.users({groupId: ' + groupId + '})"><i class="fa fa-users"></i> </a>';
                    var edit =  '<a class="pointer" ng-click="gc.clickEdit($row)"><i class="fa fa-cog"></i></a>';
                    var actions = users + edit;

                    return actions;
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

        $http.get(Routing.generate('api_get_group_searchable_fields'))
            .then(d => this.fields = d.data)

        /**********************************************************/
        /* THE FOLLOWING CALLBACKS NEED THE CURRENT OBJECT "THIS" */
        /**********************************************************/

        this._deleteCallback = function(data) {
            for (let i = 0; i < this.selected.length; i++) {
                this.alerts.push({
                    type: 'success',
                    msg: this.translate('group_removed', {group: this.selected[i].name})
                });
            }

            this.dataTableOptions.paging.count -= this.selected.length;
            this.ClarolineAPIService.removeElements(this.selected, this.groups);
            this.selected.splice(0, this.selected.length);
        }.bind(this)

        this._onSearch = function(searches) {
            this.savedSearch = searches;
            this.ClarolineSearchService.find('api_get_search_groups', searches, 0, this.dataTableOptions.paging.size).then(d => {
                this.groups = d.data.groups;
                this.dataTableOptions.paging.count = d.data.total;
            })
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

    closeAlert(index) {
        this.alerts.splice(index, 1);
    }

    clickNew() {
        const modalInstance = this.$uibModal.open({
            templateUrl: Routing.generate('api_get_create_group_form', {'_format': 'html'}),
            controller: 'CreateGroupModalController',
            controllerAs: 'cgfm'
        })

        modalInstance.result.then(result => {
            if (!result) return;
            this.groups.push(result);
            this.dataTableOptions.paging.count = this.groups.length;

            this.alerts.push({
                type: 'success',
                msg: this.translate('group_created', {group: result.name})
            });
        })
    }

    clickEdit(group) {
        const modalInstance = this.$uibModal.open({
            templateUrl: Routing.generate('api_get_edit_group_form', {'_format': 'html', 'group': group.id}) + '?bust=' + Math.random().toString(36).slice(2),
            controller: 'EditGroupModalController',
            controllerAs: 'egfm'
        });

        modalInstance.result.then(result => {
            if (!result) return;
            //dirty but it works
            console.log(result);
            this.groups = this.ClarolineAPIService.replaceById(result, this.groups);
        });
    }

   paging(offset, size) {
        this.ClarolineSearchService.find('api_get_search_groups', this.savedSearch, offset, size).then(d => {
            const groups = d.data.groups;

            //I know it's terrible... but I have no other choice with this table.
            for (let i = 0; i < offset * size; i++) {
                groups.unshift({});
            }

            this.groups = groups;
            this.dataTableOptions.paging.count = d.data.total;
        })
    }

    clickDelete() {
        const url = Routing.generate('api_delete_groups') + '?' + this.generateQsForSelected();

        let groups = '';

        for (let i = 0; i < this.selected.length; i++) {
            groups +=  this.selected[i].name
            if (i < this.selected.length - 1) groups += ', ';
        }

        this.ClarolineAPIService.confirm(
            {url: url, method: 'DELETE'},
            this._deleteCallback,
            this.translate('delete_groups'),
            this.translate('delete_groups_confirm', {group_list: groups})
        );
    }
}
