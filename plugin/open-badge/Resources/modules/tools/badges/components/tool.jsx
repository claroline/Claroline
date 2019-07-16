import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'
import {BadgeTabActions, BadgeTab as BadgeTabComponent} from '#/plugin/open-badge/tools/badges/badge/components/badge-tab'
import {MyBadges as MyBadgeTabComponent} from '#/plugin/open-badge/tools/badges/badge/components/my-badges'
import {ParametersForm} from '#/plugin/open-badge/tools/badges/parameters/components/parameters'

const Tool = props =>
  <TabbedPageContainer
    path={props.path}
    title={trans('open-badge-management', {}, 'tools')}
    redirect={[
      {from: '/', exact: true, to: '/badges'}
    ]}

    tabs={[
      {
        icon: 'fa fa-user',
        title: trans('my_badges'),
        path: '/my-badges',
        displayed: props.currentContext.type === 'desktop',
        content: MyBadgeTabComponent
      }, {
        icon: 'fa fa-book',
        title: trans('badges'),
        path: '/badges',
        actions: () => {
          const ActionsWithPath = <BadgeTabActions path={props.path}/>

          return ActionsWithPath
        },
        content: () => {
          const ComponentWithPath = <BadgeTabComponent path={props.path}/>

          return ComponentWithPath
        },
        displayed: props.currentContext.type !== 'profile'
      }, {
        icon: 'fa fa-cog',
        title: trans('parameters'),
        path: '/parameters',
        onlyIcon: true,
        //only for admin
        displayed: props.currentContext.type === 'administration',
        content: () => {
          const ParametersWithPath = <ParametersForm path={props.path}/>

          return ParametersWithPath
        }
      }, {
        icon: 'fa fa-book',
        title: trans('profile'),
        path: '/profile/:id',
        content: BadgeTabComponent,
        displayed: props.currentContext.type === 'profile'
      }
    ]}
  />

export {
  Tool
}
