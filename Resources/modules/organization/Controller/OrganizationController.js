export default class OrganizationController {
    constructor($http, OrganizationAPIService, $uibModal) {
        this.$http = $http;
        this.OrganizationAPIService = OrganizationAPIService
        this.$uibModal = $uibModal
        this.organizations = []
        this.treeOptions = {}
        OrganizationAPIService.findAll().then(d => this.organizations = d.data)
    }

    deleteOrganization(organization) {
        this.OrganizationAPIService.delete(organization.id).then(d => this.removeOrganization(this.organizations, organization.id))
    }

    addRootOrganization() {
        this.OrganizationAPIService.create('Organization' + Math.random(), '').then(d => this.organizations.push(d.data))
    }

    addDepartment(organization) {
        this.OrganizationAPIService.create('Organization' + Math.random(), organization.id).then(d => {
            if (organization.children === undefined) organization.children = [];
            organization.children.push(d.data);
        });
    }

    parametersOrganization(organization) {
        this.$uibModal.open({
            templateUrl: Routing.generate('api_get_edit_organization_form', {'organization': organization.id, '_format': 'html'}) + '?bust=' + Math.random().toString(36).slice(2),
            controller: 'EditModalController',
            resolve: {
                organizations: () => { return this.organizations },
                organization: () => { return organization }
            }
        });
    }

    editOrganization(organization) {
        this.OrganizationAPIService.editName(organization);
    }

    removeOrganization(organizations, organizationId) {
        for (var i = 0; i < organizations.length; i++) {
            if (organizations[i].children) removeOrganization(organizations[i].children, organizationId);

            if (organizations[i].id === organizationId) {
                organizations.splice(i, 1);
            }
        }
    }
}
