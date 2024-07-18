import React from 'react'

import {ContextEditor} from '#/main/app/context/editor/containers/main'

import {WorkspaceEditorOverview} from '#/main/app/contexts/workspace/editor/components/overview'
import {WorkspaceEditorAppearance} from '#/main/app/contexts/workspace/editor/components/appearance'
import {WorkspaceEditorActions} from '#/main/app/contexts/workspace/editor/components/actions'
import {WorkspaceEditorPermissions} from '#/main/app/contexts/workspace/editor/components/permissions'

const WorkspaceEditor = () =>
  <ContextEditor
    overviewPage={WorkspaceEditorOverview}
    appearancePage={WorkspaceEditorAppearance}
    permissionsPage={WorkspaceEditorPermissions}
    actionsPage={WorkspaceEditorActions}
  />

export {
  WorkspaceEditor
}
