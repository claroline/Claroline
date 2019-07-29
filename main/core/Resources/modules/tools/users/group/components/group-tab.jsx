import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/tools/users/store'
import {actions} from '#/main/core/tools/users/group/store'
import {Groups} from '#/main/core/tools/users/group/components/groups'
import {Group} from '#/main/core/tools/users/group/components/group'

const GroupTabComponent = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/groups',
        exact: true,
        component: Groups
      }, {
        path: '/groups/form/:id?',
        component: Group,
        onEnter: (params) => props.openForm(params.id || null, props.collaboratorRole)
      }
    ]}
  />

GroupTabComponent.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired,
  collaboratorRole: T.object
}

const GroupTab = connect(
  state => ({
    path: toolSelectors.path(state),
    collaboratorRole: toolSelectors.contextData(state).roles.find(role => role.translationKey === 'collaborator')
  }),
  dispatch => ({
    openForm(id = null, collaboratorRole) {
      const defaultValue = {
        organization: null, // retrieve it with axel stuff
        roles: [collaboratorRole]
      }

      dispatch(actions.open(selectors.STORE_NAME + '.groups.current', id, defaultValue))
    }
  })
)(GroupTabComponent)

export {
  GroupTab
}
