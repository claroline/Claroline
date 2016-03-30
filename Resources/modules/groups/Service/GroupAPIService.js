export default class GroupAPIService {
    constructor($http, ClarolineAPIService) {
        this.$http = $http
        this.ClarolineAPIService = ClarolineAPIService
    }

    create(newGroup) {
        const data = this.ClarolineAPIService.formSerialize('group_form', newGroup);

        return this.$http.post(
            Routing.generate('api_post_group', {'_format': 'html'}),
            data,
            {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
        )
    }

    edit(group) {
        const data = this.ClarolineAPIService.formSerialize('group_form', group);

        return this.$http.put(
            Routing.generate('api_put_group', {'_format': 'html', 'group': group.id}),
            data,
            {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
        )
    }

    find(groupId) {
        return this.$http.get(Routing.generate('api_get_group', {'group': groupId}));
    }

    importMembers(formData, group) {
        return this.$http.post(
            Routing.generate('api_group_members_import', {group: group.id}),
            formData,
            {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }
        )
    }
}
