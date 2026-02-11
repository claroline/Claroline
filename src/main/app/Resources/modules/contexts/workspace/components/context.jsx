import React from 'react'
import get from 'lodash/get'

import {addRecent} from '#/main/app/history'
import {ContextMain} from '#/main/app/context'
import {AppContext as AppContextTypes} from '#/main/app/context/prop-types'

import {route} from '#/main/core/workspace/routing'
import {WorkspaceLoading} from '#/main/app/contexts/workspace/components/loading'
import {WorkspaceEditor} from '#/main/app/contexts/workspace/editor/containers/main'
import {WorkspaceError} from '#/main/app/contexts/workspace/components/error'

const WorkspaceContext = (props) =>
  <ContextMain
    {...props}
    editor={WorkspaceEditor}
    errorPage={WorkspaceError}
    loadingPage={WorkspaceLoading}
    onOpen={(contextData) => {
      addRecent(contextData.id, 'workspace', route(contextData), contextData.name, get(contextData, 'meta.description'), contextData.thumbnail)
    }}
  />

WorkspaceContext.propTypes = AppContextTypes.propTypes

export {
  WorkspaceContext
}
