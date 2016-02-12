export default class CreateModalController {
    
    constructor(GroupAPI, $uibModalInstance, $uibModal) {
        this.GroupAPI = GroupAPI
        this.$uibModalInstance = $uibModalInstance
        this.$uibModal = $uibModal
        this.group = {}
    }
    
    submit() {
        this.GroupAPI.create(this.group).then(
            d => $uibModalInstance.close(d.data),
            d => {
                if (d.status === 400) { 
                    this.$uibModalInstance.close();
                    this.$uibModal.open({
                        template: d.data,
                        controller: () => CreateModalController,
                        bindToController: true
                    })
                }
            }
        )
    }
}