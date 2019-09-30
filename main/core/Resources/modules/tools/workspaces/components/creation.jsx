import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/workspace/routing'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {WorkspaceForm} from '#/main/core/workspace/components/form'

const Logs = props =>
  <div className="panel panel-default">
    <div className="panel-heading">
      <h4 className="panel-title">
        {trans('log')}
      </h4>
    </div>

    <div className="panel-body">
      <pre>
        {props.data.log}
      </pre>
    </div>
  </div>

Logs.propTypes = {
  data: T.object.isRequired
}

class WorkspaceCreation extends Component {
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

          if (isEmpty(props.logData)) {
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
        name="workspaces.creation"
        buttons={true}
        save={{
          type: CALLBACK_BUTTON,
          callback: () => {
            this.props.save().then(workspace =>
              this.props.history.push(route(workspace))
            )
            this.refreshLog()
            this.setState({refresh: true})
          }
        }}
        cancel={{
          type: LINK_BUTTON,
          target: this.props.path,
          exact: true
        }}
      >
        {!isEmpty(this.props.logData) &&
          <Logs data={this.props.logData} />
        }
      </WorkspaceForm>
    )}
}

WorkspaceCreation.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  path: T.string.isRequired,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  models: T.object.isRequired,
  logData: T.object,

  loadLog: T.func.isRequired,
  updateProp: T.func.isRequired,
  save: T.func.isRequired
}

WorkspaceCreation.defaultProps = {
  workspace: WorkspaceTypes.defaultProps
}

export {
  WorkspaceCreation
}
