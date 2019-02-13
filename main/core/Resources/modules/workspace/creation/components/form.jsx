import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {withRouter} from '#/main/app/router'
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
            this.props.save(this.props.workspace, this.props.history)
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
  history: T.object,
  updateProp: T.func,
  save: T.func,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  models: T.array.isRequired,
  logData: T.object
}

WorkspaceComponent.defaultProps = {
  workspace: WorkspaceTypes.defaultProps
}

const ConnectedForm = withRouter(connect(
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
    save(workspace, history) {
      dispatch(actions.save(workspace, history))
    }
  })
)(WorkspaceComponent))

export {
  ConnectedForm as WorkspaceForm
}
