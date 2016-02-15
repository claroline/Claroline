export default class CreateGroupModalController {
    constructor(GroupAPIService, $uibModalInstance, $uibModal) {
        this.GroupAPIService = GroupAPIService
        this.$uibModalInstance = $uibModalInstance
        this.$uibModal = $uibModal
        this.group = {}
    }
    
    submit() {
        this.GroupAPIService.create(this.group).then(
            d => this.$uibModalInstance.close(d.data),
            d => {
                if (d.status === 400) { 
                    this.$uibModalInstance.close();
                    this.$uibModal.open({
                        template: d.data,
                        controller: 'CreateGroupModalController',
                        bindToController: true
                    })
                }
            }
        )
    }
}