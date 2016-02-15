export default class modalController {
    constructor(OrganizationAPIService, organizations, organization, $uibModalInstance, $uibModal, ClarolineAPIService) {
        this.OrganizationAPIService = OrganizationAPIService
        this.organizations = organizations
        this.$uibModalInstance = $uibModalInstance
        this.$uibModal = $uibModal
        this.ClarolineAPIService = ClarolineAPIService
        this.organizationId = organization.id
    }

    submit() {
        this.OrganizationAPIService.update(this.organizationId, this.organization).then(
            d => {
                this.$uibModalInstance.close();
                this.ClarolineAPIService.replaceById(d.data, organizations);
            },
            d => {
                if (d.status === 400) {
                    this.$uibModalInstance.close();
                    $uibModal.open({
                        template: d.data,
                        controller: 'EditModalController',
                        controllerAs: 'modal',
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