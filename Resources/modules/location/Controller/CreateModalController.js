export class CreateModalController {
    constructor(LocationAPIService, locations, $uibModal, $uibModalInstance) {
        this.LocationAPIService = LocationAPIService
        this.locations = locations
        this.$uibModal = $uibModal
        this.$uibModalInstance = $uibModalInstance
        this.location = {}
    }

    submit() {
        this.LocationAPIService.create(this.location).then(d => {
                $this.uibModalInstance.close();
                locations.push(d.data);
            },
            function errorHandler (d) {
                if (d.status === 400) {
                    $this.uibModalInstance.close();
                    this.$uibModal.open({
                        template: d.data,
                        controller: 'CreateModalController',
                        bindToController: true,
                        resolve: {
                            locations: () => { return location }
                        }
                    })
                }
            }
        );
    } 
}