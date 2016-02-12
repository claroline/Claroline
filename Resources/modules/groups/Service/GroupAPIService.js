export default class GroupAPIService {
    constructor($http, clarolineAPI) {
        this.$http = $http
        this.clarolineAPI = clarolineAPI
    }

    create(newGroup) {
        const data = this.clarolineAPI.formSerialize('group_form', newGroup);

        return $http.post(
            Routing.generate('api_post_group', {'_format': 'html'}),
            data,
            {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
        )
    }

    edit(group) {
        const data = clarolineAPI.formSerialize('group_form', group);

        return this.$http.put(
            Routing.generate('api_put_group', {'_format': 'html', 'group': group.id}),
            data,
            {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
        )
    }

    find(groupId) {
        return this.$http.get(Routing.generate('api_get_group', {'group': groupId}));
    }
}