import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/core/router'

import {actions} from '#/main/core/administration/user/group/actions'
import {Group,  GroupActions}  from '#/main/core/administration/user/group/components/group.jsx'
import {Groups, GroupsActions} from '#/main/core/administration/user/group/components/groups.jsx'

const GroupTabActions = () =>
  <Routes
    routes={[
      {
        path: '/groups',
        exact: true,
        component: GroupsActions
      }, {
        path: '/groups/add',
        exact: true,
        component: GroupActions
      }, {
        path: '/groups/:id',
        component: GroupActions
      }
    ]}
  />

const GroupTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/groups',
        exact: true,
        component: Groups
      }, {
        path: '/groups/add',
        exact: true,
        onEnter: () => props.openForm(),
        component: Group
      }, {
        path: '/groups/:id',
        onEnter: (params) => props.openForm(params.id),
        component: Group
      }
    ]}
  />

GroupTabComponent.propTypes = {
  openForm: T.func.isRequired
}

const GroupTab = connect(
  null,
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open('groups.current', id))
    }
  })
)(GroupTabComponent)

export {
  GroupTabActions,
  GroupTab
}
