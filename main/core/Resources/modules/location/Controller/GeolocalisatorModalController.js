export default class GeolocalisatorModalController {
  geolocate() {
    const coordinates = document.getElementById('location_form_coordinates').value
    const key = document.getElementById('geolocate-key').dataset.key

    this.LocationAPIService.geolocate(coordinates, key).then(response => {
      const country = response.data.results[0].address_components.find(comp => comp.types.indexOf('country') > -1).long_name
      const pc = response.data.results[0].address_components.find(comp => comp.types.indexOf('postal_code') > -1).long_name
      const street = response.data.results[0].address_components.find(comp => comp.types.indexOf('route') > -1).long_name
      const town = response.data.results[0].address_components.find(comp => comp.types.indexOf('locality') > -1).long_name

      document.getElementById('location_form_country').value = country
      document.getElementById('location_form_town').value = town
      document.getElementById('location_form_pc').value = pc
      document.getElementById('location_form_street').value = street
      document.getElementById('location_form_streetNumber').value = 0
    })
  }
}
