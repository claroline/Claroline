export default class ImportMembersModalController {
    constructor(GroupAPIService, $uibModalInstance, $uibModal, group) {
        this.GroupAPIService = GroupAPIService
        this.$uibModalInstance = $uibModalInstance
        this.$uibModal = $uibModal
        this.group = group
    }

    submit() {
        const formData = new FormData(document.getElementById('csv-group-members-form'))
        this.GroupAPIService.importMembers(formData, this.group).then(
            d => this.$uibModalInstance.close(d.data),
            d => {
                if (d.status === 400) {
                    alert("Bad form")
                }
            }
        )
    }
}
