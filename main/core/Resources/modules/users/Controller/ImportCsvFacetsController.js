export default class ImportCsvFacetsController {
    constructor($uibModalInstance, $uibModal, UserAPIService, ClarolineAPIService) {
        this.$uibModalInstance = $uibModalInstance
        this.$uibModal = $uibModal
        this.UserAPIService = UserAPIService
        this.ClarolineAPIService = ClarolineAPIService
    }

    submit() {
        const formData = new FormData(document.getElementById('csv-facets'))
        this.UserAPIService.importCsvFacets(formData).then(
            d => this.$uibModalInstance.close(d.data),
            d => {
                if (d.status === 400) {
                    ClarolineAPIService.errorModal()
                }
            }
        )
    }
}
