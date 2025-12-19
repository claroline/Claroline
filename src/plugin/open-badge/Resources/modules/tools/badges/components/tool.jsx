import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {constants as toolConstants} from '#/main/core/tool/constants'
import {Tool} from '#/main/core/tool'

import {Assertions} from '#/plugin/open-badge/tools/badges/components/assertions'
import {BadgeList}  from '#/plugin/open-badge/tools/badges/badge/components/list'
import {BadgeShow} from '#/plugin/open-badge/tools/badges/badge/containers/show'
import {BadgesEditor} from '#/plugin/open-badge/tools/badges/editor/containers/main'
import {BadgeEditor} from '#/plugin/open-badge/badge/editor/containers/main'

const BadgeTool = (props) =>
  <Tool
    {...props}
    menu={[
      {
        name: 'my-badges',
        label: trans('my_badges', {}, 'badge'),
        target: props.path,
        type: LINK_BUTTON,
        displayed: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.contextData, 'meta.model'),
        exact: true
      }, {
        name: 'all-badges',
        label: trans('all_badges', {}, 'badge'),
        target: props.path+'/all',
        type: LINK_BUTTON
      }
    ]}
    editor={BadgesEditor}
    pages={[
      {
        path: '/',
        component: Assertions,
        exact: true,
        disabled: props.contextType === toolConstants.TOOL_WORKSPACE && get(props.contextData, 'meta.model', false)
      }, {
        path: '/all',
        component: BadgeList
      }, {
        path: '/:id/edit',
        component: BadgeEditor
      }, {
        path: '/:id',
        onEnter: (params) => {
          props.openBadge(params.id)
          props.openAssertion(params.id)
        },
        component: BadgeShow,
        exact: true
      }
    ]}
    redirect={[
      {from: '/', exact: true, to: '/all', disabled: !get(props.contextData, 'meta.model', false)}
    ]}
  />

BadgeTool.propTypes = {
  path: T.string.isRequired,
  contextType: T.string,
  contextData: T.object,
  openBadge: T.func.isRequired,
  openAssertion: T.func.isRequired
}

export {
  BadgeTool
}
