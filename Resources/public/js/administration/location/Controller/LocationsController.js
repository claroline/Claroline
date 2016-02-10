var controller =  function(
    $http,
    locationAPI,
    $uibModalStack,
    $uibModal
) {
    console.log('init Location Controller');
    var translate = function(key) {
        return window.Translator.trans(key, {}, 'platform');
    }
    this.locations = undefined;

    var removeLocation = function(location) {
        var index = this.locations.indexOf(location);
        if (index > -1 ) this.locations.splice(index, 1);
    }.bind(this)

    this.createForm = function() {
        $uibModal.open({
            templateUrl: Routing.generate('api_get_create_location_form', {'_format': 'html'}),
            controller: 'CreateModalController',
            resolve: {
                locations: function() {
                    return this.locations;
                }
            }
        });
    }.bind(this);

    this.editLocation = function(location) {
        $uibModal.open({
            //bust = no cache
            templateUrl: Routing.generate('api_get_edit_location_form', {'_format': 'html', 'location': location.id}) + '?bust=' + Math.random().toString(36).slice(2),
            controller: 'EditModalController',
            resolve: {
                locations: function() {
                    return this.locations;
                },
                location: function() {
                    return location;
                }
            }
        });
    }.bind(this)

    this.removeLocation = function(location) {
        locationAPI.delete(location.id).then(function(d) {
            removeLocation(location);
        });
    }

    locationAPI.findAll().then(function(d) {
        this.locations = d.data;
    }.bind(this));

    this.columns = [
        {
            name: translate('name'),
            prop: 'name',
            canAutoResize: false
        },
        {
            name: translate('address'),
            cellRenderer: function() {
                return '<div>{{ $row.street_number}}, {{ $row.street }}, {{ $row.pc }}, {{ $row.town }}, {{ $row.country }}</div>';
            }
        },
        {
            name: translate('actions'),
            cellRenderer: function() {
                return '<button class="btn-primary btn-xs" ng-click="lc.editLocation($row)" style="margin-right: 8px;"><i class="fa fa-pencil-square-o"></i></button><button class="btn-danger btn-xs" ng-click="lc.removeLocation($row)"><i class="fa fa-trash"></i></button>';
            }
        },
        {
            name: translate('coordinates'),
            cellRenderer: function() {
                return '<div>' + translate('latitude') + ': {{ $row.latitude }} | ' + translate('longitude') + ': {{ $row.longitude }} </div>'
            }
        }
    ];

    this.dataTableOptions = {
        scrollbarV: true,
        columnMode: 'force',
        headerHeight: 50,
        footerHeight: 50,
        columns: this.columns
    };
};

angular.module('LocationManager').controller('LocationController', ['$http', 'locationAPI', '$uibModalStack', '$uibModal', controller]);