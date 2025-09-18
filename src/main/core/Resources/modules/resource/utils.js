import get from 'lodash/get'
import uniq from 'lodash/uniq'

import {param} from '#/main/app/config'
import {getActions as getPluginsActions, getApp, getApps} from '#/main/app/plugins'

function getResources() {
  return getApps('resources')
}

function getResource(name) {
  return getApp('resources', name)()
}

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
    .find(type => type.name === get(resourceNode, 'meta.type'))
}

function supportEvaluation(resourceNode) {
  const resourceType = getType(resourceNode)

  return !!resourceType && resourceType.evaluation
}

function supportScore(resourceNode) {
  const resourceType = getType(resourceNode)

  return !!resourceType && resourceType.score
}

function supportAttempts(resourceNode) {
  const resourceType = getType(resourceNode)

  return !!resourceType && resourceType.attempts
}

function supportDownload(resourceNode) {
  const resourceType = getType(resourceNode)

  return !!resourceType && resourceType.downloadable
}

/**
 * Gets the list of available actions for a resource.
 *
 * @param {Array}   resourceNodes   - the current resource node(s)
 * @param {object}  nodesRefresher  - an object containing methods to update the node context
 * @param {string}  path            - the UI path where the resource is opened
 * @param {object}  currentUser     - the authenticated user
 * @param {boolean} withDefault     - include the default action (most of the time, it's not useful to get it)
 *
 * @return {Promise.<Array>}
 */
function getActions(resourceNodes, nodesRefresher, path, currentUser = null, withDefault = false) {
  const actions = [
    getPluginsActions('resource', resourceNodes, nodesRefresher, path, currentUser, withDefault)
  ]
  if (1 === resourceNodes.length) {
    // add custom actions of the type
    actions.push(getPluginsActions(get(resourceNodes[0], 'meta.type'), resourceNodes, nodesRefresher, path, currentUser, withDefault))
  } else {
    // check if all the selected are of the same type to get their custom actions
    const types = uniq(resourceNodes.map(resourceNode => get(resourceNode, 'meta.type')))
    if (1 === types.length && types[0]) {
      actions.push(getPluginsActions(types[0], resourceNodes, nodesRefresher, path, currentUser, withDefault))
    }
  }

  return Promise.all(actions).then((loadedActions) => loadedActions.reduce((current, acc) => acc.concat(current), []))
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
  return getActions([resourceNode], resourceNode, path, currentUser, true)
    // only get the default one
    .then(actions => actions.find(action => action.default))
}

export {
  getResources,
  getResource,
  getType,
  getTypes,
  supportEvaluation,
  supportScore,
  supportAttempts,
  supportDownload,
  getActions,
  getDefaultAction
}
