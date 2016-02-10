var service = function($http, clarolineAPI) {
    return {
        create: function(newGroup) {
            var data = clarolineAPI.formSerialize('group_form', newGroup);

            return $http.post(
                Routing.generate('api_post_group', {'_format': 'html'}),
                data,
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            );
        },
        edit: function(group) {
            var data = clarolineAPI.formSerialize('group_form', group);

            return $http.put(
                Routing.generate('api_put_group', {'_format': 'html', 'group': group.id}),
                data,
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            )
        },
        find: function(groupId) {
            return $http.get(Routing.generate('api_get_group', {'group': groupId}));
        }
    }
};

angular.module('GroupsManager').factory('GroupAPI', service);