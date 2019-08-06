import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {Path} from '#/main/core/tools/dashboard/path/components/path'

class Paths extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentPath: null
    }
  }

  componentDidMount() {
    this.props.fetchPathsData(this.props.workspaceId)
  }

  render() {
    return (
      <div>
        {this.props.trackings.map((tracking, index) =>
          <Path
            key={`path-tracking-${index}`}
            path={tracking.path}
            steps={tracking.steps}
            opened={tracking.path.id === this.state.currentPath}
            openPath={() => {
              if (this.state.currentPath === tracking.path.id) {
                this.setState({currentPath: null})
              } else {
                this.props.invalidateEvaluations()
                this.setState({currentPath: tracking.path.id})
              }
            }}
            showStepDetails={this.props.showStepDetails}
          />
        )}
      </div>
    )
  }
}

Paths.propTypes = {
  workspaceId: T.string.isRequired,
  trackings: T.arrayOf(T.shape({
    path: T.shape({
      id: T.string,
      name: T.string,
      resourceId: T.string
    }),
    steps: T.arrayOf(T.shape({
      step: T.shape({
        id: T.string,
        title: T.string
      }),
      users: T.arrayOf(T.shape({
        id: T.string,
        username: T.string,
        firstName: T.string,
        lastName: T.string,
        name: T.string
      }))
    }))
  })),
  fetchPathsData: T.func.isRequired,
  invalidateEvaluations: T.func.isRequired,
  showStepDetails: T.func.isRequired
}

export {
  Paths
}
