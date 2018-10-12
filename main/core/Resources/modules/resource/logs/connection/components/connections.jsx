import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {ListData} from '#/main/app/content/list/containers/data'

import {ConnectionList} from '#/main/core/resource/logs/connection/components/connection-list'

const ConnectionsComponent = (props) =>
  <ListData
    name="connections.list"
    fetch={{
      url: ['apiv2_log_connect_resource_list', {resource: props.resourceId}],
      autoload: true
    }}
    definition={ConnectionList.definition}
    card={ConnectionList.card}
  />

ConnectionsComponent.propTypes = {
  resourceId: T.number
}

const Connections = connect(
  state => ({
    resourceId: state.resourceId
  })
)(ConnectionsComponent)

export {
  Connections
}
