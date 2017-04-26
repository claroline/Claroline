/* global Routing */

export default class OrganizationAPIService {
  constructor($http, ClarolineAPIService) {
    this.$http = $http
    this.ClarolineAPIService = ClarolineAPIService
  }

  findAll() {
    return this.$http.get(Routing.generate('api_get_organizations'))
  }

  create(name, parent) {
    var data = this.ClarolineAPIService.formSerialize(
            'organization_form',
      {
        'name': name,
        'parent': parent
      }
        )
    return this.$http.post(
            Routing.generate('api_post_organization'),
            data,
            {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
        )
  }

  move(organization, parent) {
    const parentId = parent ? parent.id: 0

    return this.$http.get(Routing.generate('api_move_organization', {organization: organization.id, parent: parentId}))
  }

  delete(organizationId) {
    return this.$http.delete(Routing.generate('api_delete_organization', {'organization': organizationId}))
  }

  update(organizationId, organization) {
    var data = this.ClarolineAPIService.formSerialize('organization_form', organization)

    return this.$http.put(
            Routing.generate('api_put_organization', {'organization': organizationId, '_format': 'html'}),
            data,
            {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
        )
  }
}
