import React from 'react'

//import {DragDropContext} from 'react-dnd'
//import {default as TouchBackend} from 'react-dnd-touch-backend'

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

//const ToolDnD = DragDropContext(TouchBackend({ enableMouseEvents: true }))(Tool)

export {
  Tool as WorkspaceTool
}
