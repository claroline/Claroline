import GeolocalisatorModalController from './GeolocalisatorModalController'

export default class CreateLocationModalController extends GeolocalisatorModalController {
  constructor(LocationAPIService, locations, $uibModal, $uibModalInstance) {
    super()
    this.LocationAPIService = LocationAPIService
    this.locations = locations
    this.$uibModal = $uibModal
    this.$uibModalInstance = $uibModalInstance
    this.location = {}
  }

  submit() {
    this.LocationAPIService.create(this.location).then(
            d => {
              this.$uibModalInstance.close(d.data)
            },
            d => {
              if (d.status === 400) {
                this.$uibModalInstance.close()
                this.$uibModal.open({
                  template: d.data,
                  controller: 'CreateLocationModalController',
                  controllerAs: 'clfm',
                  bindToController: true,
                  resolve: {
                    locations: () => { return location }
                  }
                })
              }
            }
        )
  }
}
