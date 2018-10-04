import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {ListData} from '#/main/app/content/list/containers/data'

// import {select as workspaceSelect} from '#/main/core/workspace/selectors'
import {ConnectionList} from '#/main/core/workspace/logs/connection/components/connection-list'

const ConnectionsComponent = (props) =>
  <ListData
    name="connections.list"
    fetch={{
      url: ['apiv2_log_connect_workspace_list', {workspace: props.workspaceId}],
      autoload: true
    }}
    definition={ConnectionList.definition}
    card={ConnectionList.card}
  />

ConnectionsComponent.propTypes = {
  workspaceId: T.number
}

const Connections = connect(
  state => ({
    workspaceId: state.workspaceId
    // workspaceUuid: workspaceSelect.workspace(state).uuid
  })
)(ConnectionsComponent)

export {
  Connections
}
