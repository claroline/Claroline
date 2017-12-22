import './CreateLocationModalController'

/* global Routing */

export default class LocationController {
  constructor($http, LocationAPIService, $uibModal, ClarolineAPIService) {
    this.$http = $http
    this.LocationAPIService = LocationAPIService
    this.$uibModal = $uibModal
    this.locations = undefined
    this.ClarolineAPIService = ClarolineAPIService

    const columns = [
      {
        name: this.translate('name'),
        prop: 'name',
        canAutoResize: false
      },
      {
        name: this.translate('address'),
        cellRenderer: function () {
          return '<div>{{ $row.street_number}}, {{ $row.street }}, {{ $row.pc }}, {{ $row.town }}, {{ $row.country }}</div>'
        }
      },
      {
        name: this.translate('actions'),
        cellRenderer: function () {
          return '<button class="btn-primary btn-xs" ng-click="lc.editLocation($row)" style="margin-right: 8px;"><i class="fa fa-pencil-square-o"></i></button><button class="btn-danger btn-xs" ng-click="lc.removeLocation($row)"><i class="fa fa-trash"></i></button>'
        }
      },
      {
        name: this.translate('coordinates'),
        cellRenderer: function () {
          var gmaplink = ''
                    //var gmapurl = 'https://www.google.be/maps/@' + scope.$row.latitude + ',' + scope.$row.longitude;
                    //var gmaplink = '<a href="' + gmapurl + '"><i class="fa fa-globe"></i></a>';
                    //does not work yet

          return '<div>' + this.translate('latitude') + ': {{ $row.latitude }} | ' + this.translate('longitude') + ': {{ $row.longitude }} |  ' + gmaplink + ' </div>'
        }.bind(this)
      }
    ]

    this.dataTableOptions = {
      scrollbarV: false,
      columnMode: 'force',
      headerHeight: 50,
      footerHeight: 50,
      columns: columns
    }

    this.LocationAPIService.findAll().then(d => this.locations = d.data.data)
  }

  translate(key) {
    return window.Translator.trans(key, {}, 'platform')
  }

  removeLocation(location) {
    this.LocationAPIService.delete(location.id).then(() => {
      this.removeLocationCallback(location)
    })
  }

  removeLocationCallback(location) {
    const index = this.locations.indexOf(location)
    if (index > -1 ) this.locations.splice(index, 1)
  }

  createForm() {
    const modal = this.$uibModal.open({
      templateUrl: Routing.generate('api_get_location_create_form', {'_format': 'html'}),
      controller: 'CreateLocationModalController',
      controllerAs: 'clfm',
      resolve: {
        locations: () => { return this.locations }
      }
    })

    modal.result.then(result => {
      if (!result) return
      this.locations.push(result)
    })
  }

  editLocation(location) {
    const modal = this.$uibModal.open({
      templateUrl: Routing.generate('api_get_location_edit_form', {'_format': 'html', 'location': location.id}) + '?bust=' + Math.random().toString(36).slice(2),
      controller: 'EditLocationModalController',
      controllerAs: 'elfm',
      resolve: {
        locations: () => { return this.locations },
        location: () => { return location }
      }
    })

    modal.result.then(result => {
      if (!result) return
      this.ClarolineAPIService.replaceById(result, this.locations)
    })
  }
}
