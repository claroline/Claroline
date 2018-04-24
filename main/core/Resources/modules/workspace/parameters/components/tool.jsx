import React from 'react'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {ParametersActions, ParametersTab} from '#/main/core/workspace/parameters/parameters/parameters.jsx'
import {DisplayActions, DisplayTab} from '#/main/core/workspace/parameters/parameters/display.jsx'

const Parameters = () =>
  <TabbedPageContainer
    title={trans('parameters', {}, 'tools')}
    redirect={[
      {from: '/', exact: true, to: '/parameters'}
    ]}

    tabs={[
      {
        icon: 'fa fa-save',
        title: trans('activate_account'),
        path: '/parameters',
        actions: ParametersActions,
        content: ParametersTab
      }, {
        icon: 'fa fa-picture',
        title: trans('workspace_display'),
        path: '/display',
        actions: DisplayActions,
        content: DisplayTab
      }
    ]}
  />

export {
  Parameters
}
