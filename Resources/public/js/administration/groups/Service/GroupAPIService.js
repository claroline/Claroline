var service = function($http, clarolineAPI) {
    return {
        create: function(newGroup) {
            var data = clarolineAPI.formSerialize('group_form', newGroup);

            return $http.post(
                Routing.generate('api_post_group', {'_format': 'html'}),
                data,
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            );
        }
    }
};

angular.module('GroupsManager').factory('GroupAPI', service);