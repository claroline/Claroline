/**
 * Resources primary edit controller
 */

import angular from 'angular/index'

import ResourcesPrimaryBaseCtrl from './ResourcesPrimaryBaseCtrl'

export default class ResourcesPrimaryEditCtrl extends ResourcesPrimaryBaseCtrl {
  constructor(url, ResourceService, $scope, Translator, ConfirmService) {
    super(url, ResourceService)

    this.scope           = $scope
    this.confirmService  = ConfirmService
    this.Translator = Translator

    /**
     * Show or Hide the primary resources panel
     * @type {boolean}
     */
    this.collapsed = false

    /**
     * Configuration for the Claroline Resource Picker
     * @type {object}
     */
    this.resourcePicker = {
      // A step can allow be linked to one primary Resource, so disable multi-select
      isPickerMultiSelectAllowed: false,

      // Do not allow Path and Activities as primary resource to avoid Inception
      typeBlackList: [ 'innova_path', 'activity' ],

      // On select, set the primary resource of the step
      callback: (nodes) => {
        if (angular.isObject(nodes)) {
          for (var nodeId in nodes) {
            if (nodes.hasOwnProperty(nodeId)) {
              var node = nodes[nodeId]

              // Initialize a new Resource object (parameters : claro type, mime type, id, name)
              var resource = this.ResourceService.newResource(node[1], node[2], nodeId, node[0])
              if (!this.ResourceService.exists(this.resources, resource)) {
                // While only one resource is authorized, empty the resources array
                this.resources.splice(0, this.resources.length)

                // Resource is not in the list => add it
                this.resources.push(resource)
              }

              break // We need only one node, so only the first one will be kept
            }
          }

          this.scope.$apply()

          // Remove checked nodes for next time
          nodes = {}
        }
      }
    }
  }

  /**
   * Delete resource
   * @type {object}
   */
  removeResource(resource) {
    this.confirmService.open({
      title:         this.Translator.trans('resource_delete_title',   { resourceName: resource.name }, 'path_wizards'),
      message:       this.Translator.trans('resource_delete_confirm', {}                             , 'path_wizards'),
      confirmButton: this.Translator.trans('resource_delete',         {}                             , 'path_wizards')
    },
    // Confirm success callback
    () => {
      // Remove from resources
      for (let i = 0; i < this.resources.length; i++) {
        if (resource.id === this.resources[i].id) {
          this.resources.splice(i, 1)
          break
        }
      }
    })
  }
}
