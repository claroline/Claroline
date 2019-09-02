import React from 'react'

import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {Routes} from '#/main/app/router'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

import {trans} from '#/main/app/intl/translation'
import {ParametersForm} from '#/plugin/open-badge/tools/badges/parameters/components/parameters'
import {Badge}  from '#/plugin/open-badge/tools/badges/badge/components/badge'
import {Assertions} from '#/plugin/open-badge/tools/badges/assertion/components/list'
import {Badges}  from '#/plugin/open-badge/tools/badges/badge/components/list'
import {BadgeViewer} from '#/plugin/open-badge/tools/badges/badge/components/viewer'
import {BadgeForm} from '#/plugin/open-badge/tools/badges/badge/components/form'
import {AssertionForm} from '#/plugin/open-badge/tools/badges/assertion/components/form'

const Tool = props =>
  <ToolPage
    actions={[
      {
        name: 'new',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_badge', {}, 'openbadge'),
        target: `${props.path}/new`,
        primary: true,
        //only for organizationManager
        displayed: true
      }
    ]}
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {path: '/new',        render: () => trans('add_badge', {}, 'openbadge'), disabled: false},
          {path: '/my-badges', render: () => trans('my_badges', {}, 'openbadge')},
          {path: '/badges',     render: () => trans('badges', {}, 'openbadge')},
          {path: '/badges/:id', render: () => trans('view', {})},
          {path: '/badges/:id/form', render: () => trans('edit', {})},
          {path: '/badges/:badgeId/assertions/:id', render: () => trans('assertions', {}, 'openbadge')},
          {path: '/parameters',    render: () => trans('parameters', {})}
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/new',
          disabled: false,
          component: Badge,
          onEnter: () => props.openBadge(null, props.currentContext.data)
        }, {
          path: '/my-badges',
          render: () => {
            const MyBadges = (
              <Assertions
                url={['apiv2_assertion_current_user_list']}
                name={selectors.STORE_NAME + '.badges.mine'}
              />
            )

            return MyBadges
          }
        }, {
          path: '/badges',
          render: () => {
            const AllBadges = (
              <Badges
                url={props.currentContext.type === 'workspace' ? ['apiv2_badge-class_workspace_badge_list', {workspace: props.currentContext.data.uuid}]: ['apiv2_badge-class_list']}
                name={selectors.STORE_NAME +'.badges.list'}
              />
            )

            return AllBadges
          },
          exact: true
        }, {
          path: '/badges/:id',
          render: () => {
            const BadgeViewerComponent = (
              <BadgeViewer/>
            )

            return BadgeViewerComponent
          },
          onEnter: (params) => props.openBadge(params.id, props.currentContext.data),
          exact: true
        }, {
          path: '/badges/:id/form',
          render: () => {
            const BadgeEditorComponent = (
              <BadgeForm/>
            )

            return BadgeEditorComponent
          },
          onEnter: (params) => props.openBadge(params.id, props.currentContext.data)
        }, {
          path: '/badges/:badgeId/assertion/:id',
          component: AssertionForm,
          onEnter: (params) => props.openAssertion(params.id),
          exact: true
        }, {
          path: '/parameters',
          component: ParametersForm
        }
      ]}

      redirect={[
        {from: '/', exact: true, to: '/badges'}
      ]}
    />
  </ToolPage>

export {
  Tool
}
