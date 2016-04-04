export default class ConfirmModalController {
    constructor(callback, urlObject, title, content, $http, $uibModalInstance) {
        this.callback = callback
        this.urlObject = urlObject
        this.title = title
        this.content = content
        this.$http = $http
        this.$uibModalInstance = $uibModalInstance
    }

    submit() {
        this.$http(this.urlObject).then(
            d => {
                this.$uibModalInstance.close();
                this.callback(d.data);
            },
            d => {
                alert('An error occured');
                this.$uibModalInstance.close();
            }
        );
    }
}