import React from 'react'
import get from 'lodash/get'

import {addRecent} from '#/main/app/history'
import {ContextMain} from '#/main/app/context'
import {AppContext as AppContextTypes} from '#/main/app/context/prop-types'

import {route} from '#/main/core/workspace/routing'
import {WorkspaceForbidden} from '#/main/app/contexts/workspace/containers/forbidden'
import {WorkspaceLoading} from '#/main/app/contexts/workspace/components/loading'
import {WorkspaceNotFound} from '#/main/app/contexts/workspace/components/not-found'
import {WorkspaceEditor} from '#/main/app/contexts/workspace/editor/containers/main'

const WorkspaceContext = (props) =>
  <ContextMain
    {...props}
    editor={WorkspaceEditor}
    loadingPage={WorkspaceLoading}
    notFoundPage={WorkspaceNotFound}
    forbiddenPage={WorkspaceForbidden}
    onOpen={(contextData) => {
      addRecent(contextData.id, 'workspace', route(contextData), contextData.name, get(contextData, 'meta.description'), contextData.thumbnail)
    }}
  />

WorkspaceContext.propTypes = AppContextTypes.propTypes

export {
  WorkspaceContext
}
