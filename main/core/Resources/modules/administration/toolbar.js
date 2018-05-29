/**
 * Administration toolbar.
 *
 * NB.
 * This is a standard app for now because it's out of our main react context.
 * It will be removed when the admin container will be available
 * (only the component will be kept)
 */

import {bootstrap} from '#/main/app/bootstrap'
import {makeReducer} from '#/main/app/store/reducer'

import {AdministrationToolbar} from '#/main/core/administration/components/toolbar'

bootstrap(
  '.administration-toolbar-container',
  AdministrationToolbar,
  {
    // the current opened tool
    openedTool: makeReducer(null, {}),

    // the available tools in the administration for the current user
    tools: makeReducer([], {})
  }
)
