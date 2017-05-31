import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {registerModalTypes} from '#/main/core/layout/modal'
import {Page, PageHeader, PageContent} from '#/main/core/layout/page/components/page.jsx'
import {ResourceActions} from '#/main/core/layout/resource/components/resource-actions.jsx'

import {MODAL_RESOURCE_PROPERTIES, EditPropertiesModal} from '#/main/core/layout/resource/components/modal/edit-properties.jsx'
import {MODAL_RESOURCE_RIGHTS, EditRightsModal} from '#/main/core/layout/resource/rights/components/modal/edit-rights.jsx'

class Resource extends Component {
  constructor(props) {
    super(props)

    // register modals
    registerModalTypes([
      [MODAL_RESOURCE_PROPERTIES, EditPropertiesModal],
      [MODAL_RESOURCE_RIGHTS,     EditRightsModal]
    ])

    // open resource in fullscreen if configured
    this.state = {
      fullscreen: this.props.resourceNode.parameters.fullscreen
    }

    this.toggleFullscreen = this.toggleFullscreen.bind(this)
  }

  toggleFullscreen() {
    this.setState({
      fullscreen: !this.state.fullscreen
    })
  }

  render() {
    return (
      <Page
        className="resource-page"
        embedded={this.props.embedded}
        fullscreen={this.state.fullscreen}

        modal={this.props.modal}
        fadeModal={this.props.fadeModal}
        hideModal={this.props.hideModal}
      >
        <PageHeader
          className="resource-header"
          title={this.props.resourceNode.name}
        >
          <ResourceActions
            resourceNode={this.props.resourceNode}
            editMode={this.props.editMode}
            edit={this.props.edit}
            save={this.props.save}
            customActions={this.props.customActions}
            fullscreen={this.state.fullscreen}
            toggleFullscreen={this.toggleFullscreen}
            togglePublication={this.props.togglePublication}
            showModal={this.props.showModal}
            fadeModal={this.props.fadeModal}
            updateNode={this.props.updateNode}
          />
        </PageHeader>

        <PageContent>
          {this.props.children}
        </PageContent>
      </Page>
    )
  }
}

Resource.propTypes = {
  resourceNode: T.shape({
    name: T.string.isRequired,
    parameters: T.shape({
      fullscreen: T.bool.isRequired
    }).isRequired
  }).isRequired,
  embedded: T.bool,
  children: T.node.isRequired,
  modal: T.shape({
    type: T.string,
    fading: T.bool.isRequired,
    props: T.object.isRequired
  }),
  showModal: T.func.isRequired,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired,

  customActions: T.array.isRequired,
  editMode: T.bool,
  edit: T.oneOfType([T.func, T.string]).isRequired,
  save: T.object.isRequired,

  togglePublication: T.func.isRequired,
  updateNode: T.func.isRequired
}

Resource.defaultProps = {
  embedded: false
}

export {Resource}
