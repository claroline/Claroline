var organizationManager = angular.module('organizationManager', ['ui.tree', 'clarolineAPI', 'ui.bootstrap', 'ui.bootstrap.tpls']);
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

//we should have a helper for this kind of code...
organizationManager.controller('EditModalController', function(organizationAPI, $scope, organizations, organization, $uibModalStack, $uibModal, clarolineAPI) {
    $scope.organization = {};

    $scope.submit = function() {
        organizationAPI.update(organization.id, $scope.organization).then(
            function successHandler (d) {
                $uibModalStack.dismissAll();
                clarolineAPI.replaceById(d.data, organizations);
            },
            function errorHandler (d) {
                if (d.status === 400) {
                    $uibModalStack.dismissAll();
                    $uibModal.open({
                        template: d.data,
                        controller: 'EditModalController',
                        resolve: {
                            organizations: function() {
                                return organizations;
                            },
                            organization: function() {
                                return organization;
                            }
                        }
                    });
                }
            }
        );
    }
});

organizationManager.controller('OrganizationController', function(
    $scope,
    $log,
    $http,
    organizationAPI,
    clarolineAPI,
    $uibModalStack,
    $uibModal
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
        organizationAPI.delete(organization.id).then(function(d) {
            removeOrganization($scope.organizations, organization.id);
        });
    }

    $scope.addDepartment = function(organization) {
        organizationAPI.create('Organization' + Math.random(), organization.id).then(function(d) {
            if (organization.children === undefined) organization.children = [];
            organization.children.push(d.data);
        });
    }

    $scope.parametersOrganization = function(organization) {
        $uibModal.open({
            templateUrl: Routing.generate('api_get_edit_organization_form', {'organization': organization.id, '_format': 'html'}) + '?bust=' + Math.random().toString(36).slice(2),
            controller: 'EditModalController',
            resolve: {
                organizations: function() {
                    return $scope.organizations;
                },
                organization: function() {
                    return organization;
                }
            }
        });
    }

    $scope.editOrganization = function(organization) {
        organizationAPI.editName(organization);
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
});