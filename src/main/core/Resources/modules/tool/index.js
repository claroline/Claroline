import get from 'lodash/get'

import {ToolPage} from '#/main/core/tool/components/page'
import {ToolMain as Tool} from '#/main/core/tool/components/main'
import {ToolEditor} from '#/main/core/tool/editor'
import {ToolDashboard} from '#/main/core/tool/dashboard'
import {ToolOverview} from '#/main/core/tool/components/overview'
import {constants} from '#/main/core/tool/constants'
import {selectors} from '#/main/core/tool/store'
import {CommandPalette} from '#/main/core/tool/command-palette'
import {route} from '#/main/core/tool/routing'

/**
 * Declare a new tool to the application.
 *
 * NB1. Tool MUST be registered in the `plugin.js` file of its plugin.
 * NB2. Tool component tree MUST start with the `Tool` component
 */
function declareTool(ToolComponent, commands) {
  return {
    component: ToolComponent,
    commands: commands,
    permissions: {
      open: {
        order: 0,
        actions: [
          'Voir et ouvrir l\'outil'
        ]
      },
      edit: {
        order: 10,
        actions: [
          'Modifier les paramètres de l\'outil'
        ]
      },
      administrate: {
        order: 15,
        actions: [
          'Modifier les permissions des utilisateurs dans l\'outil'
        ]
      }
    },

    addPermissions(permissions) {
      const permNames = Object.keys(permissions)
      permNames.map(perm => {
        this.permissions[perm] = {
          order: permissions[perm].order || get(this.permissions[perm], 'order', []),
          actions: get(this.permissions[perm], 'actions', []).concat(permissions[perm].actions || [])
        }
      })

      return this
    }
  }
}

// Export the public elements of the tool module
export {
  Tool,
  ToolEditor,
  ToolDashboard,
  ToolOverview,
  ToolPage,
  constants,
  selectors,
  declareTool,
  CommandPalette,
  route
}
