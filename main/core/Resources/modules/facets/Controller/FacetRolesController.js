export default class FacetRolesController {
  constructor ($uibModalInstance, platformRoles, facet) {
    this.$uibModalInstance = $uibModalInstance
    this.platformRoles = platformRoles
    this.facet = facet
  }

  onSubmit () {
    this.$uibModalInstance.close(this.facet)
  }
}
