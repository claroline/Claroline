var organizationApi = function($http, clarolineAPI) {
    return {
        findAll: function() {
            return $http.get(Routing.generate('api_get_organizations'));
        },
        create: function(name, parent) {
            var data = clarolineAPI.formSerialize(
                'organization_form',
                {
                    'name': name,
                    'parent': parent
                }
            );
            return $http.post(
                Routing.generate('api_post_organization'),
                data,
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            );
        },
        editName: function(organization) {
            var data = clarolineAPI.formSerialize(
                'organization_form',
                {'name': organization.name}
            );
            return $http.put(
                Routing.generate('api_put_organization_name', {'organization': organization.id}),
                data,
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            );
        },
        delete: function(organizationId) {
            return $http.delete(Routing.generate('api_delete_organization', {'organization': organizationId}));
        },
        update: function(organizationId, organization) {
            var data = clarolineAPI.formSerialize('organization_form', organization);

            return $http.put(
                Routing.generate('api_put_organization', {'organization': organizationId, '_format': 'html'}),
                data,
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            );
        }
    }
};

angular.module('OrganizationManager').factory('organizationAPI', organizationApi);