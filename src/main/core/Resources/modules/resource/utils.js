import identity from 'lodash/identity'
import merge from 'lodash/merge'
import omit from 'lodash/omit'
import uniq from 'lodash/uniq'
import uniqBy from 'lodash/uniqBy'

import {param} from '#/main/app/config'
import {getApps} from '#/main/app/plugins'
import {trans} from '#/main/app/intl/translation'

import {hasPermission} from '#/main/app/security'

function getTypes() {
  return param('resources.types')
}

/**
 * Get the type implemented by a resource node.
 *
 * @param {object} resourceNode
 *
 * @return {object}
 */
function getType(resourceNode) {
  return param('resources.types')
    .find(type => type.name === resourceNode.meta.type)
}

/**
 * Loads the available actions apps from configuration.
 *
 * @param {Array}  resourceNodes  - the current resource node(s)
 * @param {Array}  actions        - the list of actions to load
 * @param {object} nodesRefresher - an object containing methods to update the node context
 * @param {string} path           - the UI path where the resource is opened
 * @param {object} currentUser    - the authenticated user
 *
 * @return {Promise.<Array>}
 */
function loadActions(resourceNodes, actions, nodesRefresher, path, currentUser) {
  // adds default refresher actions
  const refresher = Object.assign({
    add: identity,
    update: identity,
    delete: identity
  }, nodesRefresher)

  // get all actions declared
  const asyncActions = getApps('actions.resource')

  // only get implemented actions
  const implementedActions = actions.filter(action => undefined !== asyncActions[action.name])

  return Promise.all(
    // boot actions applications
    Object.keys(asyncActions).map(action => asyncActions[action]())
  ).then((loadedActions) => {
    // generates action from loaded modules
    const realActions = {}
    loadedActions.map(actionModule => {
      const generated = actionModule.default(resourceNodes, refresher, path, currentUser)

      realActions[generated.name] = generated
    })

    // merge server action with ui implementation
    return implementedActions.map(action => merge({}, omit(action, 'permission'), realActions[action.name], {
      group: trans(action.group)
    }))
  })
}

/**
 * Gets the list of available actions for a resource.
 *
 * @param {Array}   resourceNodes   - the current resource node(s)
 * @param {object}  nodesRefresher  - an object containing methods to update the node context
 * @param {string}  path            - the UI path where the resource is opened
 * @param {object}  currentUser     - the authenticated user
 * @param {boolean} withDefault     - include the default action (most of the time, it's not useful to get it)
 * @param {Array}   disabledActions
 *
 * @return {Promise.<Array>}
 */
function getActions(resourceNodes, nodesRefresher, path, currentUser = null, withDefault = false, disabledActions = []) {
  /** @var {Array} */
  const resourceTypes = uniq(resourceNodes.map(resourceNode => resourceNode.meta.type))

  const collectionActions = resourceTypes
    .reduce((accumulator, resourceType) => {
      const type = getType({meta: {type: resourceType}})

      if (type) {
        let typeActions = type.actions
          .filter(action =>
            // filter default if needed
            (withDefault || undefined === action.default || !action.default)
            // filter by permissions (the user must have perms on AT LEAST ONE node in the collection)
            && !!resourceNodes.find(resourceNode => hasPermission(action.permission, resourceNode))
          ).map(action => -1 < disabledActions.findIndex(da => da === action.name) ? Object.assign({}, action, {disabled: true}) : action)

        return uniqBy(accumulator.concat(typeActions), 'name')
      }
      return accumulator
    }, [])

  return loadActions(resourceNodes, collectionActions, nodesRefresher, path, currentUser)
}

/**
 * Gets the default action of a resource.
 *
 * @param {object} resourceNode   - the current resource node
 * @param {object} nodesRefresher - an object containing methods to update the node context
 * @param {string} path           - the UI path where the resource is opened
 * @param {object} currentUser    - the authenticated user
 *
 * @return {Promise.<object>}
 */
function getDefaultAction(resourceNode, nodesRefresher, path, currentUser = null) {
  const type = getType(resourceNode)

  if (type) {
    const defaultAction = getType(resourceNode).actions
      .find(action => action.default)


    if (hasPermission(defaultAction.permission, resourceNode)) {
      return loadActions([resourceNode], [defaultAction], nodesRefresher, path, currentUser)
        .then(loadActions => loadActions[0] || null)
    }
  }

  return Promise.resolve(null)
}

/**
 * Generates the toolbar for resources.
 *
 * @param {string|null} primaryAction - the name of a custom primary resource which will be appended to the toolbar.
 *
 * @return {string}
 */
function getToolbar(primaryAction = null) {
  let toolbar = 'edit publish unpublish'
  if (primaryAction) {
    toolbar = primaryAction + ' | ' + toolbar
  }

  return toolbar
}

export {
  getType,
  getTypes,
  getActions,
  getDefaultAction,
  getToolbar
}
