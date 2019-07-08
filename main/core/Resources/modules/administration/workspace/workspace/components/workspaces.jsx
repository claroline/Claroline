import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {actions as listActions} from '#/main/app/content/list/store'

import {getActions, getDefaultAction} from '#/main/core/workspace/utils'
import {WorkspaceList} from '#/main/core/administration/workspace/workspace/components/workspace-list'

const WorkspacesList = (props) => {
  const workspacesRefresher = {
    add: props.invalidateWorkspaces,
    update: props.invalidateWorkspaces,
    delete: props.invalidateWorkspaces
  }

  return (
    <ListData
      name="workspaces.list"
      fetch={{
        url: ['apiv2_workspace_list_managed'],
        autoload: true
      }}
      definition={WorkspaceList.definition}
      card={WorkspaceList.card}

      primaryAction={(row) => getDefaultAction(row, workspacesRefresher, '', props.currentUser)}
      actions={(rows) => getActions(rows, workspacesRefresher, '', props.currentUser)}
    />
  )
}

WorkspacesList.propTypes = {
  currentUser: T.object,
  invalidateWorkspaces: T.func.isRequired
}

const Workspaces = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  dispatch => ({
    invalidateWorkspaces() {
      dispatch(listActions.invalidateData('workspaces.list'))
    }
  })
)(WorkspacesList)

export {
  Workspaces
}
