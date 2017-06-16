/**
 * Manages Primary Resources
 */

import $ from 'jquery'

export default class ResourcesPrimaryEditDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.controller = 'ResourcesPrimaryShowCtrl'
    this.controllerAs = 'resourcesPrimaryShowCtrl'
    this.template = '<iframe id="embeddedActivity" style="height: 0; min-height: {{ resourcesPrimaryShowCtrl.height }}px;" data-ng-src="{{ resourcesPrimaryShowCtrl.resources[0].url }}" allowfullscreen></iframe>'
    this.scope = {
      resources : '=', // Resources of the Step
      height    : '='  // Min height for Resource display
    }
    this.bindToController = true
    this.link = function (scope, element) {
      $(window).on('message', function (e) {
        if (typeof e.originalEvent.data === 'string' && e.originalEvent.data.indexOf('documentHeight:') > -1) {
            // Split string from identifier
          const height = e.originalEvent.data.split('documentHeight:')[1]

            // do stuff with the height
          $(element).css('height', parseInt(height))
        }
      })
    }
  }
}
