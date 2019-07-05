import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {List} from '#/main/core/tools/desktop-parameters/components/list'
//import {TokenTabActions, TokenTabComponent} from '#/main/core/tools/desktop-parameters/token/components/token-tab'
//import {DocumentationComponent} from '#/main/core/documentation/components/documentation'

const Tool = () =>
  <TabbedPageContainer
    tabs={[
      {
        icon: 'fa fa-fw fa-cog',
        title: trans('tools'),
        path: '/',
        exact: true,
        content: List
      }/*, {
        icon: 'fa fa-fw fa-wrench',
        title: trans('tokens'),
        path: '/tokens',
        actions: TokenTabActions,
        content: TokenTabComponent
      }, {
        icon: 'fa fa-fw fa-book',
        title: trans('documentation'),
        path: '/documentation',
        content: DocumentationComponent
      }*/
    ]}
  />

export {
  Tool
}
