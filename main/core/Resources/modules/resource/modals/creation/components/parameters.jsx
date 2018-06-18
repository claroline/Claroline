import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {ContentMeta} from '#/main/app/content/meta/components/meta'
import {Await} from '#/main/app/components/await'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_RESOURCE_CREATION_RIGHTS} from '#/main/core/resource/modals/creation/components/rights'

import {getResource} from '#/main/core/resources'
import {actions, selectors} from '#/main/core/resource/modals/creation/store'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceForm} from '#/main/core/resource/components/form'

const MODAL_RESOURCE_CREATION_PARAMETERS = 'MODAL_RESOURCE_CREATION_PARAMETERS'

class ParametersModalComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      customForm: null
    }
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'parent', 'newNode', 'saveEnabled', 'save', 'configureRights')}
        icon="fa fa-fw fa-plus"
        title={trans('new_resource', {}, 'resource')}
        subtitle="2. Configurer la ressource"
      >
        <ContentMeta meta={this.props.newNode.meta} />

        <Await
          for={getResource(this.props.newNode.meta.type)()}
          then={module => {
            if (module.Creation) {
              this.setState({customForm: module.Creation()})
            }
          }}
        >
          {this.state.customForm && React.createElement(this.state.customForm.component)}
        </Await>

        <ResourceForm level={5} meta={false} name={selectors.FORM_NAME} dataPart={selectors.FORM_NODE_PART} />

        <Button
          className="modal-btn btn-link"
          type="callback"
          label={trans('edit-rights', {}, 'actions')}
          disabled={!this.props.saveEnabled}
          callback={this.props.configureRights}
        />

        <Button
          className="modal-btn btn"
          type="callback"
          primary={true}
          label={trans('create', {}, 'actions')}
          disabled={!this.props.saveEnabled}
          callback={() => this.props.save(this.props.parent, this.props.fadeModal)}
        />
      </Modal>
    )
  }
}

ParametersModalComponent.propTypes = {
  parent: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  newNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  configureRights: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const ParametersModal = connect(
  (state) => ({
    parent: selectors.parent(state),
    newNode: selectors.newNode(state),
    saveEnabled: selectors.saveEnabled(state)
  }),
  (dispatch) => ({
    save(parent, close) {
      dispatch(actions.create(parent)).then(close)
    },
    configureRights() {
      dispatch(modalActions.showModal(MODAL_RESOURCE_CREATION_RIGHTS, {}))
    }
  })
)(ParametersModalComponent)

export {
  MODAL_RESOURCE_CREATION_PARAMETERS,
  ParametersModal
}
