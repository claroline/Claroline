import merge from 'lodash/merge'
import omit from 'lodash/omit'
import uniq from 'lodash/uniq'
import uniqBy from 'lodash/uniqBy'

import {url} from '#/main/app/api'
import {param} from '#/main/app/config'
import {getApps} from '#/main/app/plugins'
import {trans} from '#/main/core/translation'

import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {hasPermission} from '#/main/core/resource/permissions'

/**
 * Get the type implemented by a resource node.
 *
 * @param {object} resourceNode
 *
 * @return {object}
 */
function getType(resourceNode) {
  return param('resourceTypes')
    .find(type => type.name === resourceNode.meta.type)
}

/**
 * Loads the available actions apps from configuration.
 *
 * @param {Array}   resourceNodes
 * @param {Array}   actions
 * @param {object}  nodesRefresher - an object containing methods to update the node context
 * @param {boolean} absolute       - tells if we need to turn internal resource links into absolute urls
 *
 * @return {Promise.<Array>}
 */
function loadActions(resourceNodes, actions, nodesRefresher, absolute = false) {
  // adds default refresher actions
  const refresher = Object.assign({
    add: (resourceNodes) => resourceNodes,
    update: (resourceNodes) => resourceNodes,
    delete: (resourceNodes) => resourceNodes
  }, nodesRefresher)

  // get all actions declared
  const asyncActions = getApps('actions')

  // only get implemented actions
  const implementedActions = actions.filter(action => undefined !== asyncActions[action.name])

  return Promise.all(
    // boot actions applications
    Object.keys(asyncActions).map(action => asyncActions[action]())
  ).then((loadedActions) => {
    // generates action from loaded modules
    const realActions = {}
    loadedActions.map(actionModule => {
      const generated = actionModule.action(resourceNodes, refresher)

      if (absolute && LINK_BUTTON === generated.type) {
        realActions[generated.name] = Object.assign({}, generated, {
          type: URL_BUTTON,
          target: url(['claro_resource_show', {
            type: resourceNodes[0].meta.type,
            id: resourceNodes[0].id
          }]) + `#${generated.target}`
        })
      } else {
        realActions[generated.name] = generated
      }
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
 * @param {Array}    resourceNodes  - the current resource node
 * @param {object}   nodesRefresher - an object containing methods to update the node context
 * @param {boolean}  absolute       - tells if we need to turn internal resource links into absolute urls
 * @param {boolean}  withDefault    - include the default action (most of the time, it's not useful to get it)
 *
 * @return {Promise.<Array>}
 */
function getActions(resourceNodes, nodesRefresher, absolute = false, withDefault = false) {
  const resourceTypes = uniq(resourceNodes.map(resourceNode => resourceNode.meta.type))

  const collectionActions = resourceTypes
    .reduce((accumulator, resourceType) => {
      let typeActions = getType({meta: {type: resourceType}}).actions
        .filter(action =>
          // filter default if needed
          (withDefault || undefined === action.default || !action.default)
          // filter by permissions (the user must have perms on AT LEAST ONE node in the collection)
          && !!resourceNodes.find(resourceNode => hasPermission(action.permission, resourceNode))
        )

      return uniqBy(accumulator.concat(typeActions), 'name')
    }, [])

  return loadActions(resourceNodes, collectionActions, nodesRefresher, absolute)
}

/**
 * Gets the default action of a resource.
 *
 * @param {object}  resourceNode
 * @param {object}  nodesRefresher
 * @param {boolean} absolute
 *
 * @return {Promise.<Array>}
 */
function getDefaultAction(resourceNode, nodesRefresher, absolute = false) {
  const defaultAction = getType(resourceNode).actions
    .find(action => action.default)

  if (hasPermission(defaultAction.permission, resourceNode)) {
    return loadActions([resourceNode], [defaultAction], nodesRefresher, absolute)
      .then(loadActions => loadActions[0] || null)
  }

  return null
}

/**
 * Generates the toolbar for resources.
 *
 * @param {string|null} primaryAction - the name of a custom primary resource which will be appended to the toolbar.
 * @param {boolean}     fullscreen    - if true, the fullscreen button will be added in the toolbar.
 *
 * @return {string}
 */
function getToolbar(primaryAction = null, fullscreen = true) {
  let toolbar = 'edit rights publish unpublish'
  if (primaryAction) {
    toolbar = primaryAction + ' | ' + toolbar
  }

  if (fullscreen) {
    toolbar += ' | fullscreen more'
  } else {
    toolbar += ' | more'
  }

  return toolbar
}

export {
  getType,
  getActions,
  getDefaultAction,
  getToolbar
}
