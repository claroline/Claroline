/* global Routing */

export default class OrganizationController {
  constructor($http, OrganizationAPIService, $uibModal, ClarolineAPIService) {
    this.$http = $http
    this.OrganizationAPIService = OrganizationAPIService
    this.$uibModal = $uibModal
    this.organizations = []
    this.treeOptions = {
      dropped: (event) => {
        this.OrganizationAPIService.move(
              event.source.nodeScope.$modelValue,
              event.dest.nodesScope.$parent.$modelValue
            )
      }
    }
    this.ClarolineAPIService = ClarolineAPIService
    OrganizationAPIService.findAll().then(d => this.organizations = d.data.data)
  }

  deleteOrganization(organization) {
    this.OrganizationAPIService.delete(organization.id).then(() => this.removeOrganization(this.organizations, organization.id))
  }

  addRootOrganization() {
    this.OrganizationAPIService.create('Organization' + Math.random(), '').then(d => this.organizations.push(d.data))
  }

  addDepartment(organization) {
    this.OrganizationAPIService.create('Organization' + Math.random(), organization.id).then(d => {
      if (organization.children === undefined) organization.children = []
      organization.children.push(d.data)
    })
  }

  parametersOrganization(organization) {
    const modal = this.$uibModal.open({
      templateUrl: Routing.generate('api_get_organization_edit_form', {'organization': organization.id, '_format': 'html'}) + '?bust=' + Math.random().toString(36).slice(2),
      controller: 'EditOrganizationModalController',
      controllerAs: 'eofm',
      resolve: {
        organizations: () => { return this.organizations },
        organization: () => { return organization }
      }
    })

    modal.result.then(result => {
      if (!result) return
            //Loop through organizations and its childre. Then we replace it if the id is the same.
      for (var i = 0; i < this.organizations.length; i++) {
        if (this.organizations[i].id === result.id) {
          this.organizations[i] = result
          return
        } else {
          this.recursiveParseOrganization(this.organizations[i], result)
        }
      }

      this.organizations = this.ClarolineAPIService.replaceById(result, this.organizations)
    })
  }

  removeOrganization(organizations, organizationId) {
    for (var i = 0; i < organizations.length; i++) {
            //recursion
      if (organizations[i].children) this.removeOrganization(organizations[i].children, organizationId)

      if (organizations[i].id === organizationId) {
        organizations.splice(i, 1)
      }
    }
  }

  recursiveParseOrganization(organization, result) {
    for (var i = 0; i < organization.children.length; i++) {
      if (organization.children[i].id === result.id) {
        organization.children[i] = result

        return
      } else {
        this.recursiveParseOrganization(organization.children[i], result)
      }
    }
  }
}
