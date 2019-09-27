import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {Routes} from '#/main/app/router'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

import {trans} from '#/main/app/intl/translation'
import {ParametersForm} from '#/plugin/open-badge/tools/badges/parameters/components/parameters'
import {Assertions} from '#/plugin/open-badge/tools/badges/assertion/components/list'
import {Badges}  from '#/plugin/open-badge/tools/badges/badge/components/list'
import {BadgeDetails} from '#/plugin/open-badge/tools/badges/badge/components/details'
import {BadgeForm} from '#/plugin/open-badge/tools/badges/badge/components/form'
import {AssertionForm} from '#/plugin/open-badge/tools/badges/assertion/components/form'

const Tool = props =>
  <ToolPage
    actions={[
      {
        name: 'new',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_badge', {}, 'badge'),
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
          {path: '/new',                            render: () => trans('new_badge', {}, 'badge')},
          {path: '/my-badges',                      render: () => trans('my_badges', {}, 'badge')},
          {path: '/badges',                         render: () => trans('all_badges', {}, 'badge')},
          {path: '/badges/:id',                     render: () => trans('view')},
          {path: '/badges/:id/form',                render: () => trans('edit')},
          {path: '/badges/:badgeId/assertions/:id', render: () => trans('assertions', {}, 'badge')},
          {path: '/parameters',                     render: () => trans('parameters')}
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/new',
          onEnter: () => props.openBadge(null, props.currentContext.data),
          component: BadgeForm
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
          onEnter: (params) => props.openBadge(params.id, props.currentContext.data),
          component: BadgeDetails,
          exact: true
        }, {
          path: '/badges/:id/form',
          onEnter: (params) => props.openBadge(params.id, props.currentContext.data),
          component: BadgeForm
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

Tool.propTypes = {
  path: T.string.isRequired,
  currentContext: T.object.isRequired,
  openBadge: T.func.isRequired,
  openAssertion: T.func.isRequired
}

export {
  Tool
}
