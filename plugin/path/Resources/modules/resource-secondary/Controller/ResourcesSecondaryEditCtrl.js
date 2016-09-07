/**
 * Resources secondary controller
 */

import angular from 'angular/index'

import ResourcesSecondaryBaseCtrl from './ResourcesSecondaryBaseCtrl'

export default class ResourcesSecondaryEditCtrl extends ResourcesSecondaryBaseCtrl {
  constructor(url, ResourceService, $scope, Translator, ConfirmService) {
    super(url, ResourceService)

    this.scope           = $scope
    this.Translator = Translator
    this.confirmService  = ConfirmService

    /**
     * Configuration for the Claroline Resource Picker
     * @type {object}
     */
    this.resourcePicker = {
      isPickerMultiSelectAllowed: true,
      callback: (nodes) => {
        this.addResources(nodes)

        // Remove checked nodes for next time
        nodes = {}
      }
    }
  }

  addResources(resources) {
    if (angular.isObject(resources)) {
      for (let nodeId in resources) {
        if (resources.hasOwnProperty(nodeId)) {
          let node = resources[nodeId]

          // Initialize a new Resource object (parameters : claro type, mime type, id, name)
          var resource = this.ResourceService.newResource(node[1], node[2], nodeId, node[0])
          if (!this.ResourceService.exists(this.resources, resource)) {
            // Resource is not in the list => add it
            this.resources.push(resource)
          }
        }
      }

      this.scope.$apply()
    }
  }

  /**
   * Delete selected resource from path
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
      // Remove from included resources
      for (let i = 0; i < this.resources.length; i++) {
        if (resource.id === this.resources[i].id) {
          this.resources.splice(i, 1)
          break
        }
      }

      // Remove from excluded resources
      for (let j = 0; j < this.excluded.length; j++) {
        if (resource.id == this.excluded[j]) {
          this.excluded.splice(j, 1)
          break
        }
      }
    })
  }

  /**
   * Toggle propagate flag
   * @param {object} resource
   */
  togglePropagation(resource) {
    resource.propagateToChildren = !resource.propagateToChildren
  }

  /**
   * Toggle excluded flag
   * @param {object} resource
   */
  toggleExcluded(resource) {
    if (resource.isExcluded) {
      // Include the resource
      for (let i = 0; i < this.excluded.length; i++) {
        if (resource.id == this.excluded[i]) {
          this.excluded.splice(i, 1)
        }
      }
    } else {
      // Exclude the resource
      this.excluded.push(resource.id)
    }

    resource.isExcluded = !resource.isExcluded
  }
}
