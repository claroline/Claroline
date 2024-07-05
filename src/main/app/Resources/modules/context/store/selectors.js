import {createSelector} from 'reselect'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {hasPermission} from '#/main/app/security'

const STORE_NAME = 'context'
const EDITOR_NAME = 'contextEditor'

/**
 * Root of the context store.
 */
const store = (state) => state[STORE_NAME] || {}

/**
 * Get the context type.
 *
 * @return string
 */
const type = createSelector(
  [store],
  (store) => store.type
)

/**
 * Get the context id.
 *
 * @return string|null
 */
const id = createSelector(
  [store],
  (store) => store.id
)

const path = createSelector(
  [type, id],
  (type, id) => id ? `/${type}/${id}` : `/${type}`
)

const data = createSelector(
  [store],
  (store) => store.data
)

/**
 * Is the context fully loaded ?
 *
 * @return bool
 */
const loaded = createSelector(
  [store],
  (store) => store.loaded
)

/**
 * Is context not found ?
 *
 * @return bool
 */
const notFound = createSelector(
  [store],
  (store) => store.notFound
)

/**
 * Get the list of all access errors to the context.
 *
 * @return object
 */
const accessErrors = createSelector(
  [store],
  (store) => store.accessErrors
)

/**
 * Can the current user manage the context ?
 *
 * @return bool
 */
const managed = createSelector(
  [data],
  (data) => hasPermission('administrate', data)
)

/**
 * Does the current user impersonate some user/role ?
 *
 * @return bool
 */
const impersonated = createSelector(
  [store],
  (store) => store.impersonated
)

/**
 *
 * Get the list of current user's roles for the context.
 */
const roles = createSelector(
  [store],
  (store) => store.roles
)

const tools = createSelector(
  [store],
  (store) => store.tools || []
)

const defaultOpening = createSelector(
  [data, tools],
  (data, tools) => {
    let defaultTool = null
    if (data && get(data, 'opening.type')) {
      if ('resource' === get(data, 'opening.type')) {
        defaultTool = `resources/${data.opening.target.slug || ''}`
      } else if ('tool' === data.opening.type) {
        defaultTool = data.opening.target
      }
    }

    // no opening config for the current context, just get the first available tool
    if (isEmpty(defaultTool) && !isEmpty(tools)) {
      if (!defaultTool || -1 === tools.findIndex(tool => defaultTool === tool.name)) {
        // no default set or the default tool is not available for the user
        // open the first available tool
        defaultTool = tools[0].name
      }
    }

    return defaultTool
  }
)

const menu = createSelector(
  [store],
  (store) => store.menu
)

const menuUntouched = createSelector(
  [menu],
  (menu) => menu.untouched
)

const menuOpened = createSelector(
  [menu],
  (menu) => menu.opened
)

export const selectors = {
  STORE_NAME,
  EDITOR_NAME,

  type,
  id,
  path,

  // selectors for context statuses
  loaded,
  notFound,
  accessErrors,

  // selectors for context security
  impersonated,
  managed,
  roles,

  // selectors for context config
  data,
  tools,
  defaultOpening,

  // selectors for menu
  menuUntouched,
  menuOpened
}