import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {constants as toolConstants} from '#/main/core/tool/constants'
import {Tool} from '#/main/core/tool'

import {Assertions} from '#/plugin/open-badge/tools/badges/assertion/components/list'
import {AssertionDetails} from '#/plugin/open-badge/tools/badges/assertion/components/details'
import {BadgeList}  from '#/plugin/open-badge/tools/badges/badge/containers/list'
import {BadgeEdit} from '#/plugin/open-badge/tools/badges/badge/containers/edit'
import {BadgeCreate} from '#/plugin/open-badge/tools/badges/badge/components/create'
import {BadgeShow} from '#/plugin/open-badge/tools/badges/badge/containers/show'
import {BadgesEditor} from '#/plugin/open-badge/tools/badges/editor/containers/main'

const BadgeTool = props =>
  <Tool
    {...props}
    styles={['claroline-distribution-plugin-open-badge-badges-tool']}
    menu={[
      {
        name: 'my-badges',
        label: trans('my_badges', {}, 'badge'),
        target: props.path+'/my',
        type: LINK_BUTTON,
        displayed: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'meta.model')
      }, {
        name: 'all-badges',
        label: trans('all_badges', {}, 'badge'),
        target: props.path,
        type: LINK_BUTTON,
        exact: true
      }
    ]}
    editor={BadgesEditor}
    pages={[
      {
        path: '/my',
        component: Assertions
      }, {
        path: '',
        component: BadgeList,
        exact: true
      }, {
        path: '/new',
        onEnter: () => props.openBadge(null, props.currentContext.data),
        component: BadgeCreate
      }, {
        path: '/:id/edit',
        onEnter: (params) => props.openBadge(params.id, props.currentContext.data),
        component: BadgeEdit
      }, {
        path: '/:id',
        onEnter: (params) => props.openBadge(params.id, props.currentContext.data),
        component: BadgeShow,
        exact: true
      }, {
        path: '/badges/:id/assertion/:assertionId',
        component: AssertionDetails,
        onEnter: (params) => props.openAssertion(params.assertionId),
        exact: true
      }
    ]}
  />

BadgeTool.propTypes = {
  path: T.string.isRequired,
  currentContext: T.object.isRequired,
  openBadge: T.func.isRequired,
  openAssertion: T.func.isRequired
}

export {
  BadgeTool
}
