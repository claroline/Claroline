import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {url} from '#/main/app/api'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {WorkspaceForm} from '#/main/core/workspace/components/form'

import {actions} from '#/main/core/workspace/creation/store/actions'
import {Logs} from '#/main/core/workspace/creation/components/logs'

class WorkspaceComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      refresh: false
    }
  }

  refreshLog() {
    const props = this.props

    if (this.state.refresh) {
      if (!props.logData.end) {
        let loader = setInterval(() => {

          clearInterval(loader)

          props.loadLog(props.workspace.code)

          if (isEmpty(props.log)) {
            this.setState({refresh: false})
          }
        }, 1500)
      }
    }
  }

  componentDidUpdate() {
    this.refreshLog()
  }

  render() {
    return (
      <WorkspaceForm
        level={3}
        name="workspaces.current"
        models={this.props.models}
        buttons={true}
        save={{
          type: 'callback',
          callback: () => {
            this.props.save()
            this.refreshLog()
            this.setState({refresh: true})
          }
        }}
      >
        <Logs />
      </WorkspaceForm>
    )}
}

WorkspaceComponent.propTypes = {
  loadLog: T.func,
  updateProp: T.func,
  save: T.func,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  models: T.object.isRequired,
  logData: T.object
}

WorkspaceComponent.defaultProps = {
  workspace: WorkspaceTypes.defaultProps
}

const ConnectedForm = connect(
  (state) => ({
    models: state.models,
    log: state.workspaces.creation.log,
    workspace: formSelect.data(formSelect.form(state, 'workspaces.current')),
    logData: state.workspaces.creation.log //always {} for some reason
  }),
  (dispatch, ownProps) =>({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    },
    loadLog(filename) {
      dispatch(actions.load(filename))
    },
    save() {
      dispatch(formActions.save('workspaces.current', ['apiv2_workspace_create'])).then((response) => {
        window.location.href = url(['claro_workspace_open', {workspaceId: response.id}])
      })
    }
  })
)(WorkspaceComponent)

export {
  ConnectedForm as WorkspaceForm
}
