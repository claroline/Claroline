export default class ConfirmModalController {
    constructor($uibModalInstance, content) {
        this.$uibModalInstance = $uibModalInstance
        this.content = content
    }

    cancel() {
        this.$uibModalInstance.dismiss('cancel');
    }

    confirm() {
        this.$uibModalInstance.close();
    }
}
