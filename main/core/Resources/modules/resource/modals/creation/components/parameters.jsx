import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {Await} from '#/main/app/components/await'
import {ContentMeta} from '#/main/app/content/meta/components/meta'

import {getResource} from '#/main/core/resources'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceForm} from '#/main/core/resource/components/form'

import {selectors} from '#/main/core/resource/modals/creation/store'

class ResourceParameters extends Component {
  constructor(props) {
    super(props)

    this.state = {
      customForm: null
    }
  }

  render() {
    return (
      <div>
        <ContentMeta meta={this.props.resourceNode.meta} />

        <Await
          for={getResource(this.props.resourceNode.meta.type)()}
          then={module => {
            if (module.Creation) {
              this.setState({customForm: module.Creation()})
            }
          }}
        >
          {this.state.customForm && React.createElement(this.state.customForm.component)}
        </Await>

        <ResourceForm level={5} meta={false} name={selectors.STORE_NAME} dataPart={selectors.FORM_NODE_PART} />
      </div>
    )
  }
}

ResourceParameters.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  ResourceParameters
}
