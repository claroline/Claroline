import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Assertions} from '#/plugin/open-badge/tools/badges/assertion/components/list'
import {Badges}  from '#/plugin/open-badge/tools/badges/badge/components/list'
import {BadgeDetails} from '#/plugin/open-badge/tools/badges/badge/components/details'
import {BadgeForm} from '#/plugin/open-badge/tools/badges/badge/components/form'
import {AssertionDetails} from '#/plugin/open-badge/tools/badges/assertion/components/details'
import {MODAL_TRANSFER} from '#/plugin/open-badge/modals/transfer'
import {Tool} from '#/main/core/tool'

const BadgeTool = props =>
  <Tool
    {...props}
    styles={['claroline-distribution-plugin-open-badge-badges-tool']}
  >
    <ToolPage
      primaryAction="new"
      actions={[
        {
          name: 'new',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add_badge', {}, 'badge'),
          target: `${props.path}/new`,
          primary: true,
          displayed: props.canEdit
        },{
          name: 'transfer-badges',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-right-left',
          label: trans('transfer_badges', {}, 'actions'),
          modal: [MODAL_TRANSFER],
          displayed: props.canAdministrate,
          primary: false
        }
      ]}
      subtitle={
        <Routes
          path={props.path}
          routes={[
            {path: '/new',        render: () => trans('new_badge', {}, 'badge')},
            {path: '/my-badges',  render: () => trans('my_badges', {}, 'badge')},
            {path: '/badges',     render: () => trans('all_badges', {}, 'badge')}
          ]}
        />
      }
    >
      <Routes
        path={props.path}
        redirect={[
          {from: '/', exact: true, to: '/badges'}
        ]}
        routes={[
          {
            path: '/new',
            onEnter: () => props.openBadge(null, props.currentContext.data),
            component: BadgeForm
          }, {
            path: '/my-badges',
            component: Assertions
          }, {
            path: '/badges',
            component: Badges,
            exact: true
          }, {
            path: '/badges/:id',
            onEnter: (params) => props.openBadge(params.id, props.currentContext.data),
            component: BadgeDetails,
            exact: true
          }, {
            path: '/badges/:id/edit',
            onEnter: (params) => props.openBadge(params.id, props.currentContext.data),
            component: BadgeForm
          }, {
            path: '/badges/:id/assertion/:assertionId',
            component: AssertionDetails,
            onEnter: (params) => props.openAssertion(params.assertionId),
            exact: true
          }
        ]}
      />
    </ToolPage>
  </Tool>

BadgeTool.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  canGrant: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  currentContext: T.object.isRequired,
  openBadge: T.func.isRequired,
  openAssertion: T.func.isRequired
}

export {
  BadgeTool
}
