import get from 'lodash/get'

import {param} from '#/main/app/config'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {constants} from '#/main/core/tool/constants'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as adminRoute} from '#/main/core/administration/routing'

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
  }

  return path
}

function showToolBreadcrumb(contextType, contextData) {
  if (param('display.breadcrumb')) {
    if (constants.TOOL_WORKSPACE === contextType) {
      return !!get(contextData, 'breadcrumb.displayed')
    }

    return true
  }

  return false
}

export {
  getToolBreadcrumb,
  showToolBreadcrumb
}
