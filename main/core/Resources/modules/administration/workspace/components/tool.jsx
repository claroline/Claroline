import React from 'react'

import {t} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/page/containers/tabbed-page.jsx'

import {WorkspaceTab, WorkspaceTabActions} from '#/main/core/administration/workspace/workspace/components/workspace-tab.jsx'

const Tool = () =>
  <TabbedPageContainer
    redirect={[
      {from: '/', exact: true, to: '/workspaces'}
    ]}

    tabs={[
      {
        icon: 'fa fa-book',
        title: t('workspaces'),
        path: '/workspaces',
        actions: WorkspaceTabActions,
        content: WorkspaceTab
      }
    ]}
  />

export {
  Tool as WorkspaceTool
}
