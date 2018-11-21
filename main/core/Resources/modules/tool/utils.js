import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/core/tool/constants'
import {currentUser} from '#/main/app/security'

/**
 * Gets the path of a tool based on its rendering context.
 *
 * @param {string} toolName
 * @param {string} contextType
 * @param {object} contextData
 *
 * @return {Array}
 */
function getToolPath(toolName, contextType, contextData = {}) {
  const user = currentUser()
  const breadcrumbItems = get(contextData, 'breadcrumb.items') || []

  let path = []

  switch (contextType) {
    case constants.TOOL_DESKTOP:
      path = [
        {
          label: trans('desktop'),
          target: ['claro_desktop_open']
        }, {
          label: trans(toolName, {}, 'tools'),
          target: ['claro_desktop_open_tool', {toolName: toolName}]
        }
      ]
      break

    case constants.TOOL_WORKSPACE:
      if (user) {
        path = [
          {
            icon: 'fa fa-fw fa-atlas',
            label: trans('desktop'),
            displayed: -1 !== breadcrumbItems.indexOf('desktop'),
            target: ['claro_desktop_open']
          }, {
            label: trans('my_workspaces'),
            displayed: -1 !== breadcrumbItems.indexOf('workspaces'),
            target: ['claro_workspace_by_user']
          }
        ]
      } else {
        path = [
          {
            label: trans('public_workspaces'),
            displayed: -1 !== breadcrumbItems.indexOf('workspaces'),
            target: ['claro_workspace_list']
          }
        ]
      }

      path = path.concat([
        {
          label: contextData.name,
          displayed: -1 !== breadcrumbItems.indexOf('current'),
          target: ['claro_workspace_open', {workspaceId: contextData.id}]
        }, {
          label: trans(toolName, {}, 'tools'),
          displayed: -1 !== breadcrumbItems.indexOf('tool'),
          target: ['claro_workspace_open_tool', {workspaceId: contextData.id, toolName: toolName}]
        }
      ])
      break

    case constants.TOOL_ADMINISTRATION:
      path = [
        {
          label: trans('administration'),
          target: ['claro_admin_open']
        }, {
          label: trans(toolName, {}, 'tools'),
          target: ['claro_admin_open_tool', {toolName: toolName}]
        }
      ]
      break
  }

  return path
}

function showToolBreadcrumb(contextType, contextData) {
  if (constants.TOOL_WORKSPACE === contextType) {
    return !!get(contextData, 'breadcrumb.displayed')
  }

  return true
}

export {
  getToolPath,
  showToolBreadcrumb
}
