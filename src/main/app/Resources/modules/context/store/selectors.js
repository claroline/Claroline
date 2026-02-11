import {createSelector} from 'reselect'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import isNumber from 'lodash/isNumber'

import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as platformSelectors} from '#/main/app/platform/store/selectors'

const STORE_NAME = 'context'
const EDITOR_NAME = 'contextEditor'

/**
 * Root of the context store.
 */
const store = (state) => state[STORE_NAME]

/**
 * Get the context type.
 *
 * @return string
 */
const type = createSelector(
  [store],
  (store) => get(store, 'type')
)

/**
 * Get the context id.
 *
 * @return string|null
 */
const id = createSelector(
  [store],
  (store) => get(store, 'id')
)

const path = createSelector(
  [type, id],
  (type, id) => id ? `/${type}/${id}` : `/${type}`
)

const menuOpened = createSelector(
  [store],
  (store) => get(store, 'menuOpened', false)
)

const data = createSelector(
  [store],
  (store) => get(store, 'data', null)
)

const name = createSelector(
  [type, data],
  (type, data) => get(data, 'name', trans(type, {}, 'context'))
)

const contactEmail = createSelector(
  [platformSelectors.contactEmail, data],
  (contactEmail, data) => data && data.contactEmail ? data.contactEmail : contactEmail
)

/**
 * Is the context fully loaded?
 *
 * @return bool
 */
const loaded = createSelector(
  [store],
  (store) => get(store, 'loaded', false)
)

/**
 * Get the list of all access errors to the context.
 *
 * @return object
 */
const error = createSelector(
  [store],
  (store) => get(store, 'error')
)

const hasErrors = createSelector(
  [error],
  (error) => !isEmpty(error)
)

/**
 * Can the current user manage the context?
 *
 * @return bool
 */
const managed = createSelector(
  [data],
  (data) => hasPermission('administrate', data)
)

/**
 * Does the current user impersonate some user/role?
 *
 * @return bool
 */
const impersonated = createSelector(
  [store],
  (store) => get(store, 'impersonated', false)
)

/**
 * Get the list of current user's roles for the context.
 */
const roles = createSelector(
  [store],
  (store) => store.roles || []
)

const organizations = createSelector(
  [store],
  (store) => [].concat(get(store, 'organizations', []))
    .sort((a, b) => {
      if (a.name > b.name) {
        return 1
      }

      return -1
    })
)

const tools = createSelector(
  [store],
  (store) => get(store, 'tools', [])
)

const accessibleTools = createSelector(
  [tools],
  (tools) => [].concat(tools)
    .filter(tool => hasPermission('open', tool))
    .sort((a, b) => {
      if (isNumber(a.order) && isNumber(b.order) && a.order !== b.order) {
        return a.order - b.order
      }

      if (trans(a.name, {}, 'tools') > trans(b.name, {}, 'tools')) {
        return 1
      }

      return -1
    })
)

const visibleTools = createSelector(
  [accessibleTools],
  (accessibleTools) => accessibleTools.filter(tool => !get(tool, 'restrictions.hidden', false))
)

const toolLinks = createSelector(
  [path, data, visibleTools],
  (path, data, visibleTools) => {
    const toolLinks = visibleTools.map(tool => ({
      name: tool.name,
      type: LINK_BUTTON,
      icon: `fa fa-fw fa-${tool.icon}`,
      label: trans(tool.name, {}, 'tools'),
      target: path + '/' + tool.name,
      status: tool.status,
      subscript: tool.status ? {
        type: 'label',
        value: tool.status,
        status: 'primary'
      } : undefined
    }))

    if (hasPermission('administrate', data)) {
      // append editor
      toolLinks.push({
        name: 'parameters',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-sliders',
        label: trans('parameters'),
        target: path + '/edit'
      })
    }

    return toolLinks
  }
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
    if (!isEmpty(tools)) {
      if (!defaultTool || -1 === tools.findIndex(tool => defaultTool === tool.name)) {
        // no default set or the default tool is not available for the user
        // open the first available tool
        defaultTool = tools[0].name
      }
    }

    return defaultTool
  }
)

export const selectors = {
  STORE_NAME,
  EDITOR_NAME,

  type,
  id,
  path,
  name,

  // selectors for the context menu
  menuOpened,

  // selectors for context statuses
  loaded,
  error,
  hasErrors,

  // selectors for context security
  impersonated,
  managed,
  roles,
  organizations,

  // selectors for context config
  data,
  tools,
  contactEmail,
  accessibleTools,
  visibleTools,
  toolLinks,
  defaultOpening
}
