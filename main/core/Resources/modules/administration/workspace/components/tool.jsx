import React from 'react'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {WorkspaceTab, WorkspaceTabActions} from '#/main/core/administration/workspace/workspace/components/workspace-tab.jsx'

const Tool = () =>
  <TabbedPageContainer
    title={trans('workspace_management', {}, 'tools')}
    redirect={[
      {from: '/', exact: true, to: '/workspaces'}
    ]}

    tabs={[
      {
        icon: 'fa fa-book',
        title: trans('workspaces'),
        path: '/workspaces',
        actions: WorkspaceTabActions,
        content: WorkspaceTab
      }
    ]}
  />

export {
  Tool as WorkspaceTool
}
