import React from 'react'

import {implementPropTypes} from '#/main/app/prop-types'
import {ContextMain} from '#/main/app/context/containers/main'
import {AppContext as AppContextTypes} from '#/main/app/context/prop-types'

import {WorkspaceForbidden} from '#/main/app/contexts/workspace/containers/forbidden'
import {WorkspaceLoading} from '#/main/app/contexts/workspace/components/loading'
import {WorkspaceNotFound} from '#/main/app/contexts/workspace/components/not-found'
import {WorkspaceMenu} from '#/main/app/contexts/workspace/containers/menu'
import {WorkspaceEditor} from '#/main/app/contexts/workspace/editor/components/main'

const WorkspaceContext = (props) =>
  <ContextMain
    {...props}
    menu={WorkspaceMenu}
    editor={WorkspaceEditor}
    loadingPage={WorkspaceLoading}
    notFoundPage={WorkspaceNotFound}
    forbiddenPage={WorkspaceForbidden}
  />

implementPropTypes(WorkspaceContext, AppContextTypes)

export {
  WorkspaceContext
}
