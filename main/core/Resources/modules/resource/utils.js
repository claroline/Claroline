import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {param} from '#/main/app/config'
import {trans} from '#/main/core/translation'

// todo load dynamically
import {actions} from '#/main/core/resource/actions/actions'

/**
 * Get the type implemented by a resource node.
 *
 * @param resourceNode
 */
function getType(resourceNode) {
  return param('resourceTypes')
    .find(type => type.name === resourceNode.meta.type)
}

/**
 * Gets the list of available for a resource.
 *
 * @param resourceNode
 * @param scope
 */
function getActions(resourceNode, scope = null) {
  return getType(resourceNode).actions
    .filter(action =>
      // filter by scope
      (!scope || isEmpty(action.scope) || -1 !== action.scope.indexOf(scope))
      // filter by permissions
      && !!resourceNode.permissions[action.permission]
      // filter implemented actions only
      && undefined !== actions[action.name]
    )

    // merge server conf with ui
    .map(action => merge({}, omit(action, 'permission'), actions[action.name]([resourceNode], scope), {
      group: trans(action.group, {}, 'resource')
    }))
}

function getDefaultAction(resourceNode, scope) {
  return getActions(resourceNode, scope).find(action => action.default)
}

export {
  getType,
  getActions,
  getDefaultAction
}
