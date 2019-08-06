import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {selectors as toolSelectors} from  '#/main/core/tool/store'
import {actions, selectors} from '#/main/core/tools/dashboard/store'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {WorkspaceMetrics} from '#/main/core/workspace/components/metrics'
import {DailyActivity} from '#/main/core/tools/dashboard/components/daily-activity'
import {Resources} from '#/main/core/tools/dashboard/components/resources'

class AnalyticsComponent extends Component {
  constructor(props) {
    super(props)

    if (!this.props.analytics.loaded) {
      this.props.getAnalytics(this.props.workspace.id)
    }
  }

  render() {
    return (
      <div>
        <WorkspaceMetrics
          workspace={this.props.workspace}
          nbConnections={this.props.nbConnections}
        />

        {this.props.analytics.loaded &&
          <DailyActivity activity={this.props.analytics.data.activity} />
        }

        {this.props.analytics.loaded &&
          <Resources resourceTypes={this.props.analytics.data.resourceTypes} />
        }
      </div>
    )
  }
}

AnalyticsComponent.propTypes = {
  analytics: T.shape({
    loaded: T.bool.isRequired,
    data: T.object
  }).isRequired,
  nbConnections: T.number.isRequired,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  getAnalytics: T.func.isRequired
}

const Analytics = connect(
  state => ({
    analytics: selectors.analytics(state),
    nbConnections: selectors.nbConnections(state),
    workspace: toolSelectors.contextData(state)
  }),
  dispatch => ({
    getAnalytics: (workspaceId) => {
      dispatch(actions.getAnalyticsData('apiv2_workspace_tool_dashboard', {workspaceId}))
    }
  })
)(AnalyticsComponent)

export {
  Analytics
}
