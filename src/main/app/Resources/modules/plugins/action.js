import identity from 'lodash/identity'

import {getApps} from '#/main/app/plugins/app'

/**
 * Get the list of actions injected by plugins for an entity.
 *
 * @param {string}  entityName
 * @param {array}   entities
 * @param {object}  refresher
 * @param {string}  path        - the UI path where the entity is opened
 * @param {object}  currentUser - the authenticated user
 * @param {boolean} withDefault
 */
function getActions(entityName, entities, refresher, path, currentUser, withDefault = false) {
  // adds default refresher actions
  const finalRefresher = Object.assign({
    add: identity,
    update: identity,
    delete: identity
  }, refresher)

  // get all actions declared for workspace
  const actions = getApps('actions.'+entityName)

  return Promise.all(
    // boot actions applications
    Object.keys(actions).map(action => actions[action]())
  ).then((loadedActions) => loadedActions
    // generate action
    .map(actionModule => actionModule.default(entities, finalRefresher, path, currentUser))
    // filter default if needed
    .filter(action => (withDefault || undefined === action.default || !action.default))
  )
}

/**
 * Gets the default action of an entity.
 *
 * @param {string} entityName
 * @param {object} entity
 * @param {object} refresher
 * @param {string} path        - the UI path where the entity is opened
 * @param {object} currentUser - the authenticated user
 *
 * @return {Promise.<Array>}
 */
function getDefaultAction(entityName, entity, refresher, path, currentUser = null) {
  // load all available actions
  return getActions(entityName, [entity], refresher, path, currentUser, true)
    // only get the default one
    .then(actions => actions.find(action => action.default))
}

export {
  getActions,
  getDefaultAction
}
