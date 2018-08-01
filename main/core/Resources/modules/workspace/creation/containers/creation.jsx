import React, {Component} from 'react'
import {connect} from 'react-redux'
import {CreationForm} from '#/main/core/workspace/creation/components/creation.jsx'

class WorkspaceCreationContainer extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (<CreationForm></CreationForm>)
  }
}


const ConnectedWorkspaceCreationContainer = connect(
  null, null
)(WorkspaceCreationContainer)

export {
  ConnectedWorkspaceCreationContainer as WorkspaceCreation
}
