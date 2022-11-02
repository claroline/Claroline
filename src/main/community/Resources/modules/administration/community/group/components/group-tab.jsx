import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as baseSelectors} from '#/main/community/administration/community/store'
import {Group}   from '#/main/community/administration/community/group/components/group'
import {Groups}  from '#/main/community/administration/community/group/components/groups'
import {actions} from '#/main/community/administration/community/group/store'

const GroupTabComponent = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('groups'),
      target: `${props.path}/groups`
    }]}
    subtitle={trans('groups')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_group'),
        target: `${props.path}/groups/form`,
        primary: true
      }
    ]}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/groups',
          exact: true,
          component: Groups
        }, {
          path: '/groups/form/:id?',
          onEnter: (params) => props.openForm(params.id || null),
          component: Group
        }
      ]}
    />
  </ToolPage>

GroupTabComponent.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired
}

const GroupTab = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open(baseSelectors.STORE_NAME+'.groups.current', id))
    }
  })
)(GroupTabComponent)

export {
  GroupTab
}
