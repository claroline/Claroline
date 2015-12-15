var organizationManager = angular.module('organizationManager', ['ui.tree', 'clarolineAPI']);
var translator = window.Translator;

organizationManager.config(function ($httpProvider) {
    $httpProvider.interceptors.push(function ($q) {
        return {
            'request': function(config) {
                $('.please-wait').show();

                return config;
            },
            'requestError': function(rejection) {
                $('.please-wait').hide();

                return $q.reject(rejection);
            },  
            'responseError': function(rejection) {
                $('.please-wait').hide();

                return $q.reject(rejection);
            },
            'response': function(response) {
                $('.please-wait').hide();

                return response;
            }
        };
    });
});

organizationManager.controller('OrganizationController', function(
    $scope,
    $log,
    $http,
    organizationAPI,
    clarolineAPI
    ) {

    $scope.organizations = [];

    var removeOrganization = function(organizations, organizationId) {
        for (var i = 0; i < organizations.length; i++) {
            if (organizations[i].children) removeOrganization(organizations[i].children, organizationId);

            if (organizations[i].id === organizationId) {
                organizations.splice(i, 1);
            } 
        }
    }

    organizationAPI.findAll().then(function(d) {
        $scope.organizations = d.data;
    });

    $scope.addRootOrganization = function() {
        organizationAPI.create('Organization' + Math.random(), '').then(function(d) {
            $scope.organizations.push(d.data);
        });
    }

    $scope.deleteOrganization = function(organization) {
        console.log(organization);

        organizationAPI.delete(organization.id).then(function(d) {
            removeOrganization($scope.organizations, organization.id);
        });
    }

    $scope.addDepartment = function(organization)
    {
        organizationAPI.create('Organization' + Math.random(), organization.id).then(function(d) {
            if (organization.children === undefined) organization.children = [];
            organization.children.push(d.data);
        });
    }

    $scope.editOrganization = function(organization)
    {
        organizationAPI.edit(organization);
    }

    $scope.treeOptions = {

    };

});

organizationManager.directive('organizationslist', [
    function organizationsmanager() {
        return {
            restrict: 'E',
            templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/organization/views/organizationmanager.html',
            replace: true
        }
    }
]);

organizationManager.factory('organizationAPI', function($http, clarolineAPI) {
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
        edit: function(organization) {
            var data = clarolineAPI.formSerialize(
                'organization_form', 
                {'name': organization.name}
            );
            return $http.put(
                Routing.generate('api_put_organization', {'organization': organization.id}),
                data,
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            );
        },
        delete: function(organizationId) {
            return $http.delete(Routing.generate('api_delete_organization', {'organization': organizationId}));
        }
    }
});