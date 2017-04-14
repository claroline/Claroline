/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/
/*global Translator*/
import locationFormTemplate from '../Partial/location_form_modal.html'

export default class LocationsManagementCtrl {
  constructor($http, $uibModal, NgTableParams, ClarolineAPIService) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
    this.locations = []
    this.locationResources = []
    this.reservationResources = []
    this.tableParams = {
      locations:  new NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.locations}
      ),
      locationResources:  new NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.locationResources}
      ),
      resources:  new NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.reservationResources}
      )
    }
    this._addLocationCallback = this._addLocationCallback.bind(this)
    this._updateLocationCallback = this._updateLocationCallback.bind(this)
    this._removeLocationCallback = this._removeLocationCallback.bind(this)
    this.initialize()
  }

  _addLocationCallback(datas) {
    const locationJson = JSON.parse(datas)
    this.locations.push(this.generateLocationAddress(locationJson))
    this.tableParams['locations'].reload()
  }

  _updateLocationCallback(datas) {
    const locationJson = JSON.parse(datas)
    const index = this.locations.findIndex(l => l['id'] === locationJson['id'])

    if (index > -1) {
      this.locations[index] = this.generateLocationAddress(locationJson)
      this.tableParams['locations'].reload()
    }
  }

  _removeLocationCallback(datas) {
    const locationJson = JSON.parse(datas)
    const index = this.locations.findIndex(l => l['id'] === locationJson['id'])

    if (index > -1) {
      this.locations.splice(index, 1)
      this.tableParams['locations'].reload()
    }
  }

  initialize() {
    this.loadLocations()
  }

  loadLocations() {
    const url = Routing.generate('api_get_cursus_locations')
    this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        this.locations.splice(0, this.locations.length)
        datas.forEach(l => {
          this.locations.push(this.generateLocationAddress(l))
        })
      }
    })
  }

  loadLocationResources() {
    const url = Routing.generate('api_get_cursus_reservation_resources')
    this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        this.locationResources.splice(0, this.locationResources.length)
        datas.forEach(r => {
          r['type'] = r['resourceType']['name']
          this.locationResources.push(r)
        })
      }
    })
  }

  loadResources() {
    const url = Routing.generate('api_get_reservation_resources')
    this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        this.reservationResources.splice(0, this.reservationResources.length)
        datas.forEach(r => {
          r['type'] = r['resourceType']['name']
          this.reservationResources.push(r)
        })
      }
    })
  }

  addLocationResource(resource) {
    const url = Routing.generate('api_post_cursus_reservation_resources_tag', {resource: resource['id']})
    this.$http.post(url).then(d => {
      if (d['status'] === 200 && d['data'] === 'success') {
        this.locationResources.push(resource)
      }
    })
  }

  removeLocationResource(resourceId) {
    const url = Routing.generate('api_delete_cursus_reservation_resources_tag', {resource: resourceId})
    this.$http.delete(url).then(d => {
      if (d['status'] === 200 && d['data'] === 'success') {
        const index = this.locationResources.findIndex(l => l['id'] === resourceId)

        if (index > -1) {
          this.locationResources.splice(index, 1)
        }
      }
    })
  }

  isLocationResource(resourceId) {
    return this.locationResources.findIndex(l => l['id'] === resourceId) > -1
  }

  generateLocationAddress(location) {
    location['address'] = `
      ${location['street']}, ${location['street_number']} ${location['box_number'] ? '(' + location['box_number'] + ')' : ''}
      <br>
      ${location['pc']} ${location['town']}
      <br>
      ${location['country']}
    `

    return location
  }

  createLocation() {
    this.$uibModal.open({
      template: locationFormTemplate,
      controller: 'LocationCreationModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        title: () => { return Translator.trans('create_location', {}, 'cursus') },
        callback: () => { return this._addLocationCallback }
      }
    })
  }

  editLocation(location) {
    this.$uibModal.open({
      template: locationFormTemplate,
      controller: 'LocationEditionModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        title: () => { return Translator.trans('edit_location', {}, 'cursus') },
        location: () => { return location },
        callback: () => { return this._updateLocationCallback }
      }
    })
  }

  deleteLocation(locationId) {
    const url = Routing.generate('api_delete_cursus_location', {location: locationId})
    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      this._removeLocationCallback,
      Translator.trans('delete_location', {}, 'cursus'),
      Translator.trans('delete_location_confirm_message', {}, 'cursus')
    )
  }
}