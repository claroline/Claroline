import React from 'react'

import {implementPropTypes} from '#/main/app/prop-types'
import {ContextMain} from '#/main/app/context/containers/main'
import {AppContext as AppContextTypes} from '#/main/app/context/prop-types'

import {WorkspaceForbidden} from '#/main/app/contexts/workspace/containers/forbidden'
import {WorkspaceLoading} from '#/main/app/contexts/workspace/components/loading'
import {WorkspaceNotFound} from '#/main/app/contexts/workspace/components/not-found'
import {WorkspaceMenu} from '#/main/app/contexts/workspace/containers/menu'

const WorkspaceContext = (props) =>
  <ContextMain
    {...props}
    parent="desktop"

    menu={WorkspaceMenu}
    loadingPage={WorkspaceLoading}
    notFoundPage={WorkspaceNotFound}
    forbiddenPage={WorkspaceForbidden}
  />

implementPropTypes(WorkspaceContext, AppContextTypes)

export {
  WorkspaceContext
}
