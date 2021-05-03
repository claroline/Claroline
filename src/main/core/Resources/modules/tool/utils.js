import get from 'lodash/get'
import identity from 'lodash/identity'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {showBreadcrumb} from '#/main/app/layout/utils'
import {getApps} from '#/main/app/plugins'

import {constants} from '#/main/core/tool/constants'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as adminRoute} from '#/main/core/administration/routing'

function getActions(tool, context, toolRefresher, path, currentUser) {
  // adds default refresher actions
  const refresher = Object.assign({
    add: identity,
    update: identity,
    delete: identity
  }, toolRefresher)

  // get all actions declared for workspace
  const actions = getApps('actions.tool')

  return Promise.all(
    // boot actions applications
    Object.keys(actions).map(action => actions[action]())
  ).then((loadedActions) => loadedActions
    // generate action
    .map(actionModule => actionModule.default(tool, context, refresher, path, currentUser))
  )
}

/**
 * Gets the path of a tool based on its rendering context.
 *
 * @param {null|string} toolName
 * @param {string} contextType
 * @param {object} contextData
 *
 * @return {Array}
 */
function getToolBreadcrumb(toolName = null, contextType, contextData = {}) {
  const breadcrumbItems = get(contextData, 'breadcrumb.items') || []

  let path = []

  switch (contextType) {
    case constants.TOOL_DESKTOP:
      path = [
        {
          type: LINK_BUTTON,
          label: trans('desktop'),
          target: '/desktop'
        }
      ]

      if (toolName) {
        path.push({
          type: LINK_BUTTON,
          label: trans(toolName, {}, 'tools'),
          target: toolRoute(toolName)
        })
      }

      break

    case constants.TOOL_WORKSPACE:
      path = [
        {
          type: LINK_BUTTON,
          label: trans('desktop'),
          displayed: -1 !== breadcrumbItems.indexOf('desktop'),
          target: '/desktop'
        }, {
          type: LINK_BUTTON,
          label: trans('my_workspaces', {}, 'workspace'),
          displayed: -1 !== breadcrumbItems.indexOf('workspaces'),
          target: toolRoute('workspaces')
        }, {
          type: LINK_BUTTON,
          label: contextData.name,
          displayed: -1 !== breadcrumbItems.indexOf('current'),
          target: workspaceRoute(contextData)
        }
      ]

      if (toolName) {
        path.push({
          type: LINK_BUTTON,
          label: trans(toolName, {}, 'tools'),
          displayed: -1 !== breadcrumbItems.indexOf('tool'),
          target: workspaceRoute(contextData, toolName)
        })
      }

      break

    case constants.TOOL_ADMINISTRATION:
      path = [
        {
          type: LINK_BUTTON,
          label: trans('administration'),
          target: '/admin'
        }
      ]

      if (toolName) {
        path.push({
          type: LINK_BUTTON,
          label: trans(toolName, {}, 'tools'),
          target: adminRoute(toolName)
        })
      }

      break

    default:
      if (toolName) {
        path.push({
          type: LINK_BUTTON,
          label: trans(toolName, {}, 'tools'),
          target: `/${toolName}`
        })
      }

      break
  }

  return path
}

function showToolBreadcrumb(contextType, contextData) {
  if (showBreadcrumb()) {
    if (constants.TOOL_WORKSPACE === contextType) {
      return !!get(contextData, 'breadcrumb.displayed')
    }

    return true
  }

  return false
}

export {
  getActions,
  getToolBreadcrumb,
  showToolBreadcrumb
}
