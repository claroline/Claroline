import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {Await} from '#/main/app/components/await'
import {getResource} from '#/main/core/resources'

class ResourceMain extends Component {
  constructor(props) {
    super(props)

    this.state = {}
  }

  render() {
    return (
      <Await
        for={getResource(this.props.resourceType)()}
        then={module => {
          const resourceApp = module.App()
          if (resourceApp) {
            this.setState({
              component: resourceApp.component
            })
          }
        }}
      >
        {this.state.component && React.createElement(this.state.component)}
      </Await>
    )
  }
}

ResourceMain.propTypes = {
  resourceType: T.string.isRequired
}

export {
  ResourceMain
}
