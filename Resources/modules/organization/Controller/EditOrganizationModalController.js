export default class EditOrganizationModalController {
    constructor(OrganizationAPIService, organizations, organization, $uibModalInstance, $uibModal, ClarolineAPIService) {
        this.OrganizationAPIService = OrganizationAPIService
        this.organizations = organizations
        this.$uibModalInstance = $uibModalInstance
        this.$uibModal = $uibModal
        this.ClarolineAPIService = ClarolineAPIService
        this.organizationId = organization.id
        this.organization = {}
    }

    submit() {
        this.OrganizationAPIService.update(this.organizationId, this.organization).then(
            d => {
                this.$uibModalInstance.close(d.data);
            },
            d => {
                if (d.status === 400) {
                    this.$uibModalInstance.close();
                    $uibModal.open({
                        template: d.data,
                        controller: 'EditOrganizationModalController',
                        controllerAs: 'eofm',
                        resolve: {
                            organizations: () => { return organizations },
                            organization: () => { return organization }
                        }
                    });
                }
            }
        )
    }
}