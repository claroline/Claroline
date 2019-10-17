import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {Path} from '#/plugin/analytics/tools/dashboard/path/components/path'

class Paths extends Component {
  constructor(props) {
    super(props)
  }

  componentDidMount() {
    this.props.fetchPathsData(this.props.workspaceId)
  }

  render() {
    return (
      <Fragment>
        {this.props.tracking.map((tracking, index) =>
          <Path
            key={`path-tracking-${index}`}
            path={tracking.path}
            steps={tracking.steps}
            unstartedUsers={tracking.unstartedUsers}
            invalidateEvaluations={this.props.invalidateEvaluations}
            showStepDetails={this.props.showStepDetails}
          />
        )}
      </Fragment>
    )
  }
}

Paths.propTypes = {
  workspaceId: T.string.isRequired,
  tracking: T.arrayOf(T.shape({
    path: T.object,
    steps: T.array,
    unstartedUsers: T.array
  })),
  fetchPathsData: T.func.isRequired,
  invalidateEvaluations: T.func.isRequired,
  showStepDetails: T.func.isRequired
}

export {
  Paths
}
