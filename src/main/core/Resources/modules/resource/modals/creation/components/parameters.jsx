import React, {createElement, Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {theme} from '#/main/theme/config'

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

  componentDidMount() {
    getResource(this.props.resourceNode.meta.type).then((module) => {
      let creationApp = null
      if (module.Creation) {
        creationApp = module.Creation()
      }

      this.setState({customForm: creationApp})
    })
  }

  renderCustomForm() {
    if (this.state.customForm) {
      return (
        <Fragment>
          {createElement(this.state.customForm.component)}

          {this.state.customForm.styles && this.state.customForm.styles.map(styleName =>
            <link key={styleName} rel="stylesheet" type="text/css" href={theme(styleName)} />
          )}
        </Fragment>
      )
    }

    return null
  }

  render() {
    return (
      <ResourceForm
        level={5}
        meta={false}
        name={selectors.STORE_NAME}
        dataPart={selectors.FORM_NODE_PART}
      >
        {this.renderCustomForm()}
      </ResourceForm>
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
