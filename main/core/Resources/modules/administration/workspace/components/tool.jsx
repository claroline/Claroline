import React from 'react'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {WorkspaceTab, WorkspaceTabActions} from '#/main/core/administration/workspace/workspace/components/workspace-tab.jsx'
import {ParametersTab, ParametersTabActions} from '#/main/core/administration/workspace/parameters/components/parameters-tab.jsx'

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
      }, {
        icon: 'fa fa-cog',
        title: trans('parameters'),
        path: '/parameters',
        onlyIcon: true,
        //only for admin
        displayed: true,
        actions: ParametersTabActions,
        content: ParametersTab
      }
    ]}
  />

export {
  Tool as WorkspaceTool
}
