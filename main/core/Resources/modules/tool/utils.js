import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {constants} from '#/main/core/tool/constants'

/**
 * Gets the path of a tool based on its rendering context.
 *
 * @param {string} toolName
 * @param {string} contextType
 * @param {object} contextData
 *
 * @return {Array}
 */
function getToolBreadcrumb(toolName, contextType, contextData = {}) {
  const breadcrumbItems = get(contextData, 'breadcrumb.items') || []

  let path = []

  switch (contextType) {
    case constants.TOOL_DESKTOP:
      path = [
        {
          type: LINK_BUTTON,
          label: trans('desktop'),
          target: '/desktop'
        }, {
          type: LINK_BUTTON,
          label: trans(toolName, {}, 'tools'),
          target: '/desktop/' + toolName
        }
      ]
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
          target: '/desktop/workspaces'
        }, {
          type: LINK_BUTTON,
          label: contextData.name,
          displayed: -1 !== breadcrumbItems.indexOf('current'),
          target: '/desktop/workspaces/' + contextData.id
        }, {
          type: LINK_BUTTON,
          label: trans(toolName, {}, 'tools'),
          displayed: -1 !== breadcrumbItems.indexOf('tool'),
          target: '/desktop/workspaces/' + contextData.id + '/' + toolName
        }
      ]
      break

    case constants.TOOL_ADMINISTRATION:
      path = [
        {
          type: LINK_BUTTON,
          label: trans('administration'),
          target: '/administration'
        }, {
          type: LINK_BUTTON,
          label: trans(toolName, {}, 'tools'),
          target: '/administration/' + toolName
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
  getToolBreadcrumb,
  showToolBreadcrumb
}
