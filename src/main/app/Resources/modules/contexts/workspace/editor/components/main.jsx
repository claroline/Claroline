import React from 'react'

import {ContextEditor} from '#/main/app/context/editor/containers/main'

import {WorkspaceEditorOverview} from '#/main/app/contexts/workspace/editor/components/overview'
import {WorkspaceEditorAppearance} from '#/main/app/contexts/workspace/editor/components/appearance'
import {WorkspaceEditorHistory} from '#/main/app/contexts/workspace/editor/components/history'
import {WorkspaceEditorActions} from '#/main/app/contexts/workspace/editor/components/actions'

const WorkspaceEditor = () =>
  <ContextEditor
    overviewPage={WorkspaceEditorOverview}
    appearancePage={WorkspaceEditorAppearance}
    historyPage={WorkspaceEditorHistory}
    actionsPage={WorkspaceEditorActions}
  />

export {
  WorkspaceEditor
}
