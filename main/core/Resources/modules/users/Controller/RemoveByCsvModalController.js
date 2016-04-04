export default class RemoveByCsvModalController {
    constructor($uibModalInstance, $uibModal, UserAPIService) {
        this.$uibModalInstance = $uibModalInstance
        this.$uibModal = $uibModal
        this.UserAPIService = UserAPIService
    }

    submit() {
        const formData = new FormData(document.getElementById('csv-remove-form'))
        this.UserAPIService.removeFromCsv(formData).then(
            d => this.$uibModalInstance.close(d.data),
            d => {
                if (d.status === 400) {
                    alert("It didn't work")
                }
            }
        )
    }
}
