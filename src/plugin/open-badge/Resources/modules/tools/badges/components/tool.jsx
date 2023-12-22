import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Assertions} from '#/plugin/open-badge/tools/badges/assertion/components/list'
import {AssertionDetails} from '#/plugin/open-badge/tools/badges/assertion/components/details'
import {MODAL_TRANSFER} from '#/plugin/open-badge/modals/transfer'

import {BadgeShow} from '#/plugin/open-badge/tools/badges/containers/show'
import {BadgeCreate} from '#/plugin/open-badge/tools/badges/components/create'
import {BadgeList} from '#/plugin/open-badge/tools/badges/containers/list'
import {BadgeEdit} from '#/plugin/open-badge/tools/badges/containers/edit'

const BadgeTool = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '',
        component: BadgeList,
        exact: true
      }, {
        path: '/new',
        onEnter: () => props.openBadge(null, props.contextData),
        component: BadgeCreate,
        disabled: !props.canEdit
      }, {
        path: '/:id',
        onEnter: (params) => props.openBadge(params.id, props.contextData),
        component: BadgeShow,
        exact: true
      }, {
        path: '/:id/edit',
        onEnter: (params) => props.openBadge(params.id, props.contextData),
        component: BadgeEdit
      },


      /*{
        path: '/my-badges',
        component: Assertions
      }, {
        path: '/badges/:id/assertion/:assertionId',
        component: AssertionDetails,
        onEnter: (params) => props.openAssertion(params.assertionId),
        exact: true
      }*/
    ]}
  />

BadgeTool.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  contextData: T.object,
  openBadge: T.func.isRequired,
  openAssertion: T.func.isRequired
}

export {
  BadgeTool
}
