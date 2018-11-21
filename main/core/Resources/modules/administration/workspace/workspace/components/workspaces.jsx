import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

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
        url: ['apiv2_administrated_list'],
        autoload: true
      }}
      definition={WorkspaceList.definition}
      card={WorkspaceList.card}

      primaryAction={(row) => getDefaultAction(row, workspacesRefresher)}
      actions={(rows) => getActions(rows, workspacesRefresher)}
    />
  )
}

WorkspacesList.propTypes = {
  invalidateWorkspaces: T.func.isRequired
}

const Workspaces = connect(
  null,
  dispatch => ({
    invalidateWorkspaces() {
      dispatch(listActions.invalidateData('workspaces.list'))
    }
  })
)(WorkspacesList)

export {
  Workspaces
}
