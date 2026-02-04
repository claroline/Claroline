import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {toKey} from '#/main/app/utils/text'

import {LINK_BUTTON} from '#/main/app/buttons/link'
import {MODAL_BUTTON} from '#/main/app/buttons/modal'
import {URL_BUTTON} from '#/main/app/buttons/url'

import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

/**
 * Returns a subset of actions by their name.
 *
 * @param {string[]} actionNames
 * @param {array|Promise} actions
 *
 * @return {array|Promise}
 */
function pickActions(actionNames, actions) {
  if (Array.isArray(actions)) {
    return [].concat(actions)
      .filter(action => actionNames.includes(action.name))
  }

  return actions.then((loadedActions) => loadedActions.filter(action => actionNames.includes(action.name)))
}

function pickAction(actionName, actions) {
  if (Array.isArray(actions)) {
    return actions.find(action => action.name === actionName)
  }

  return actions.then((loadedActions) => loadedActions.find(action => action.name === actionName))
}

function pickActionSet(setName, actions) {
  if (Array.isArray(actions)) {
    return [].concat(actions)
      .filter(action => !action.set || action.set.includes(setName))
  }

  return actions.then((loadedActions) => loadedActions.filter(action => !action.set || action.set.includes(setName)))
}

function createActionDefinition(action) {
  // compute id based on received config
  let actionDef = {
    id: action.id || action.name || (typeof action.label === 'string' && toKey(action.label)) || undefined
  }

  // manage confirmation
  if (action.confirm) {
    // transform action to display confirm modal first
    const confirmDef = Object.assign({}, typeof action.confirm === 'object' ? action.confirm : {}, {
      // append some defaults from action spec
      icon: action.confirm.icon || action.icon,
      title: action.confirm.title || action.label,
      question: typeof action.confirm === 'string' ? action.confirm : action.confirm.message,
      additional: action.confirm.additional,
      dangerous: action.dangerous,

      // forward original action to the confirmation modal
      confirmAction: Object.assign({}, omit(action, 'confirm'), {
        id: actionDef.id ? `${actionDef.id}-confirm` : undefined,
        label: action.confirm.button || action.label
      })
    })

    actionDef = Object.assign(actionDef, {
      type: MODAL_BUTTON,
      modal: [MODAL_CONFIRM, confirmDef]
    })
  }

  return Object.assign({}, omit(action, 'confirm'), actionDef)
}

/**
 * Make the action a URL button to escape the embedded router.
 */
function makeAbsolute(action) {
  if (LINK_BUTTON === action.type) {
    return merge({}, action, {
      type: URL_BUTTON,
      target: url(['claro_index'])+'#'+action.target
    })
  }

  return action
}

export {
  createActionDefinition,
  makeAbsolute,
  pickActions,
  pickAction,
  pickActionSet
}
