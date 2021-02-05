import {url} from '#/main/app/api'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'
import {MODAL_RESOURCES} from '#/main/core/modals/resources'

/**
 * Moves resource nodes to the destination chosen by the user.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (resourceNodes, nodesRefresher) => ({
  name: 'move',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-arrows',
  label: trans('move', {}, 'actions'),
  modal: [MODAL_RESOURCES, {
    title: trans('select_target_directory'),
    current: 0 < resourceNodes.length && resourceNodes[0].parent ? resourceNodes[0].parent : null,
    selectAction: (selected = []) => ({
      type: ASYNC_BUTTON,
      label: trans('select', {}, 'actions'),
      request: {
        url: url(['claro_resource_collection_action', {action: 'move'}], {
          parent: selected[0] ? selected[0].id : null, // required for correct rights check in API
          ids: resourceNodes.map(resourceNode => resourceNode.id)
        }),
        request: {
          method: 'PUT',
          body: JSON.stringify({
            destination: selected[0]
          })
        },
        success: (response) => {
          nodesRefresher.delete(resourceNodes)
          nodesRefresher.add(response)
        }
      }
    }),
    filters: [{property: 'resourceType', value: 'directory', locked: true}]
  }]
})
