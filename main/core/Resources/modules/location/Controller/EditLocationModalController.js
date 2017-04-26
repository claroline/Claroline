import GeolocalisatorModalController from './GeolocalisatorModalController'

export default class EditLocationModalController extends GeolocalisatorModalController {
  constructor(LocationAPIService, locations, location, $uibModalInstance, $uibModal, ClarolineAPIService) {
    super()
    this.LocationAPIService = LocationAPIService
    this.locations = locations
    this.locationId = location.id
    this.$uibModalInstance = $uibModalInstance
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
  }

  submit() {
    this.LocationAPIService.update(this.locationId, this.location).then(
            d => {
              this.$uibModalInstance.close(d.data)
            },
            d => {
              if (d.status === 400) {
                this.$uibModalInstance.close()
                this.$uibModal.open({
                  template: d.data,
                  controller: 'EditLocationModalController',
                  controllerAs: 'elfm',
                  resolve: {
                    locations: (locations) => { return locations },
                    location: (location) => { return location}
                  }
                })
              }
            }
        )
  }
}
