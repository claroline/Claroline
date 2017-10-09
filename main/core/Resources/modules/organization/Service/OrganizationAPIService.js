/* global Routing */

export default class OrganizationAPIService {
  constructor($http, ClarolineAPIService) {
    this.$http = $http
    this.ClarolineAPIService = ClarolineAPIService
  }

  findAll() {
    return this.$http.get(Routing.generate('apiv2_organization_list_recursive'))
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

    return this.$http.patch(Routing.generate('apiv2_organization_move', {child: organization.id, parent: parentId}))
  }

  delete(organizationId) {
    return this.$http.delete(Routing.generate('apiv2_organization_delete_bulk') + '?ids[]=' +  organizationId)
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
