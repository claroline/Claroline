export class EditModalController {
    constructor(LocationAPIService, locations, location, $uibModalInstance, $uibModal, ClarolineAPIService) {
        this.LocationAPIService = LocationAPIService
        this.locations = locations
        //this.location = { } // .??
        this.$uibModalInstance = $uibModalInstance
        this.$uibModal = $uibModal
        this.ClarolineAPIService = ClarolineAPIService
    }

    submit() {
        this.LocationAPIService.update(location.id, $this.location).then(d => { 
                this.$uibModalInstance.close();
                this.ClarolineAPIService.replaceById(d.data, locations)
            },            
            function errorHandler (d) {
                if (d.status === 400) {
                    this.$uibModalInstance.close();
                    this.$uibModal.open({
                        template: d.data,
                        controller: 'EditModalController',
                        resolve: {
                            locations: () => { return locations },
                            location: () => { return location}
                        }
                    })
                }
            }
        );
    }
}