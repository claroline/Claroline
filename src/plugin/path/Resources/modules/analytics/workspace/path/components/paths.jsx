import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Path} from '#/plugin/path/analytics/workspace/path/components/path'

class Paths extends Component {
  constructor(props) {
    super(props)
  }

  componentDidMount() {
    this.props.fetchPathsData(this.props.workspaceId)
  }

  render() {
    return (
      <ToolPage
        subtitle={trans('paths_tracking')}
      >
        {this.props.tracking.map((tracking, index) =>
          <Path
            key={`path-tracking-${index}`}
            path={tracking.path}
            steps={tracking.steps}
            unstartedUsers={tracking.unstartedUsers}
            showStepDetails={this.props.showStepDetails}
          />
        )}
      </ToolPage>
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
  showStepDetails: T.func.isRequired
}

export {
  Paths
}
