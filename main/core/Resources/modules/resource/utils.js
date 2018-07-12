import merge from 'lodash/merge'
import omit from 'lodash/omit'
import uniq from 'lodash/uniq'
import uniqBy from 'lodash/uniqBy'

import {param} from '#/main/app/config'
import {getApps} from '#/main/app/plugins'
import {trans} from '#/main/core/translation'

import {hasPermission} from '#/main/core/resource/permissions'

/**
 * Get the type implemented by a resource node.
 *
 * @param {object} resourceNode
 */
function getType(resourceNode) {
  return param('resourceTypes')
    .find(type => type.name === resourceNode.meta.type)
}

/**
 * Loads the available actions apps from configuration.
 *
 * @param {Array} resourceNodes
 * @param {Array} actions
 *
 * @return {Promise}
 */
function loadActions(resourceNodes, actions) {
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
      const generated = actionModule.action(resourceNodes)
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
 * @param {Array}   resourceNodes - the current resource node
 * @param {boolean} withDefault   - include the default action (most of the time, it's not useful to get it)
 */
function getActions(resourceNodes, withDefault = false) {
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

  return loadActions(resourceNodes, collectionActions)
}

function getDefaultAction(resourceNode) {
  const defaultAction = getType(resourceNode).actions
    .find(action => action.default)

  if (hasPermission(defaultAction.permission, resourceNode)) {
    return loadActions([resourceNode], [defaultAction])
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
