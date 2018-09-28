/**
 * A workspace toolbar.
 *
 * NB.
 * This is a standard app for now because it's out of our main react context.
 * It will be removed when the workspace container will be available
 * (only the component will be kept)
 */

import {bootstrap} from '#/main/app/bootstrap'
import {makeReducer} from '#/main/app/store/reducer'

import {WorkspaceToolbar} from '#/main/core/workspace/components/toolbar'
import {selectors} from '#/main/core/workspace/modals/parameters/store'

bootstrap(
  '.workspace-toolbar-container',
  WorkspaceToolbar,
  {
    // the current workspace
    workspace: makeReducer({}, {
      ['FORM_SUBMIT_SUCCESS/' + selectors.STORE_NAME]: (state, action) => action.updatedData
    }),

    // the current opened tool
    openedTool: makeReducer(null, {}),

    // the available tools in the workspace for the current user
    tools: makeReducer([], {})
  }
)
