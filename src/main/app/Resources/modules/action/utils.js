import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import omit from 'lodash/omit'
import uniqWith from 'lodash/uniqWith'
import pickBy from 'lodash/pickBy'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/app/utils/text'

import {LINK_BUTTON} from '#/main/app/buttons/link'
import {MENU_BUTTON} from '#/main/app/buttons/menu'
import {MODAL_BUTTON} from '#/main/app/buttons/modal'
import {URL_BUTTON} from '#/main/app/buttons/url'

import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

const GROUP_SEPARATOR  = '|'
const ACTION_SEPARATOR = ' '

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
 *
 * @param {string} toolbarConfig
 */
function parseToolbar(toolbarConfig) {
  if (toolbarConfig) {
    const groups = toolbarConfig.split(GROUP_SEPARATOR)

    return groups
      .map(group => group
        .split(ACTION_SEPARATOR)
        .map(action => action.trim())
      )
  }

  return []
}

function buildToolbar(toolbarConfig, actions = [], scope, moreIcon = 'fa-ellipsis-v') {
  let toolbar = []

  // filters toolbar actions
  actions = uniqWith(actions.filter(action =>
    // only get displayed actions
    (undefined === action.displayed || !!action.displayed)
    // only get actions for the requested scope
    && (!scope || isEmpty(action.scope) || -1 !== action.scope.indexOf(scope))
  ), (a, b) => !isEmpty(a.name) && !isEmpty(b.name) && a.name === b.name)

  if (0 === actions.length) {
    return []
  }

  if (1 === actions.length) {
    // avoid creating a more dropdown if there is only one action
    return [actions]
  }

  // retrieves defined actions groups
  const config = parseToolbar(toolbarConfig)

  // we want to know if there is action that are not configured in the toolbar
  let rest = actions.slice()

  // checks if there is a `more` action to grab remaining actions
  let hasMore = false
  // we only allow one primary action in the btn bar
  // we don't limit it in the more menu
  let hasPrimaryAction = false

  if (0 !== config.length) {
    toolbar = config.map(
      // loop over each group
      (group) => group.map(
        // loop over each defined action to retrieve them
        (actionName) => {
          if ('more' === actionName) {
            hasMore = true

            // create the more action
            // we will fill menu later or remove the button if there is no remaining action
            return {
              name: 'more',
              type: MENU_BUTTON,
              icon: 'fa fa-fw ' + moreIcon,
              label: trans('show-more-actions', {}, 'actions'),
              menu: {
                align: 'end'
              }
            }
          } else {
            const pos = rest.findIndex(action => actionName === action.name)
            if (-1 !== pos) {
              const action = rest.splice(pos, 1)

              if (action[0].primary) {
                if (!hasPrimaryAction) {
                  hasPrimaryAction = true
                } else {
                  // we only keep 1st primary action
                  action[0].primary = false
                }
              }

              // return the definition of the action (nb. splice always return an array)
              return action[0]
            }
          }
        }
      ).filter(action => !!action)
    ).filter(group => 0 !== group.length)
  }

  if (0 < rest.length) {
    // append remaining actions to the configured toolbar (in a new group)
    if (hasMore) {
      // merge all remaining actions in a menu (avoid the more menu if it remains only one item)
      const groupIndex = toolbar.findIndex(group => -1 !== group.findIndex(action => 'more' === action.name))
      const actionIndex = toolbar[groupIndex].findIndex(action => 'more' === action.name)

      if (1 !== rest.length) {
        toolbar[groupIndex][actionIndex].menu.items = rest
      } else {
        // replace more action
        toolbar[groupIndex][actionIndex] = rest.pop()
      }
    } else {
      // append all remaining actions in a new group
      toolbar.push(
        rest.sort((a, b) => {
          if (!a.dangerous && b.dangerous) {
            return -1
          } else if (a.dangerous && !b.dangerous) {
            return 1
          }

          return 0
        })
      )
    }
  } else if (hasMore) {
    // all actions were configured in the toolbar, remove the more menu
    const groupIndex = toolbar.findIndex(group => -1 !== group.findIndex(action => 'more' === action.name))
    const actionIndex = toolbar[groupIndex].findIndex(action => 'more' === action.name)

    // remove action
    delete toolbar[groupIndex][actionIndex]
  }

  return toolbar
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
  buildToolbar,
  makeAbsolute,
  pickActions,
  pickAction,
  pickActionSet
}
