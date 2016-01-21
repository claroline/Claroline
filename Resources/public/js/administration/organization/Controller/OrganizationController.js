var controller = function($http, organizationAPI, clarolineAPI, $uibModalStack, $uibModal) {
    this.organizations = [];

    var removeOrganization = function(organizations, organizationId) {
        for (var i = 0; i < organizations.length; i++) {
            if (organizations[i].children) removeOrganization(organizations[i].children, organizationId);

            if (organizations[i].id === organizationId) {
                organizations.splice(i, 1);
            }
        }
    }

    organizationAPI.findAll().then(function(d) {
        this.organizations = d.data;
    }.bind(this));

    this.addRootOrganization = function() {
        organizationAPI.create('Organization' + Math.random(), '').then(function(d) {
            this.organizations.push(d.data);
        }.bind(this));
    }.bind(this);

    this.deleteOrganization = function(organization) {
        organizationAPI.delete(organization.id).then(function(d) {
            removeOrganization(this.organizations, organization.id);
        }.bind(this));
    }.bind(this);

    this.addDepartment = function(organization) {
        organizationAPI.create('Organization' + Math.random(), organization.id).then(function(d) {
            if (organization.children === undefined) organization.children = [];
            organization.children.push(d.data);
        });
    }

    this.parametersOrganization = function(organization) {
        $uibModal.open({
            templateUrl: Routing.generate('api_get_edit_organization_form', {'organization': organization.id, '_format': 'html'}) + '?bust=' + Math.random().toString(36).slice(2),
            controller: 'EditModalController',
            resolve: {
                organizations: function() {
                    return this.organizations;
                }.bind(this),
                organization: function() {
                    return organization;
                }
            }
        });
    }.bind(this);

    this.editOrganization = function(organization) {
        organizationAPI.editName(organization);
    }

    this.treeOptions = {

    };
}

angular.module('OrganizationManager').controller('OrganizationController', [
    '$http',
    'organizationAPI',
    'clarolineAPI',
    '$uibModalStack',
    '$uibModal',
    controller
]);