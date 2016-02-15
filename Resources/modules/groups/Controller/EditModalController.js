export default class EditModalController {
    constructor(GroupAPIService, $uibModalInstance, $uibModal) {
        this.GroupAPIService = GroupAPIService
        this.$uibModalInstance = $uibModalInstance
        this.$uibModal = $uibModal
        this.group = {}
    }

    submit() {
        this.GroupAPIService.edit(this.group).then(
            d => this.$uibModalInstance.close(d.data),
            d => {
                if (d.status === 400) { 
                    this.$uibModalInstance.close();
                    this.$uibModal.open({
                        template: d.data,
                        controller: 'EditModalController',
                        bindToController: true
                    })
                }
            }
        )
    }
}