import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {ResourceRights} from '#/main/core/resource/components/rights'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

import {constants} from '#/main/core/resource/modals/creation/constants'
import {ResourceType} from '#/main/core/resource/modals/creation/components/type'
import {ResourceParameters} from '#/main/core/resource/modals/creation/components/parameters'

class ResourceCreationModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentStep: 'type'
    }

    this.changeStep = this.changeStep.bind(this)
  }

  changeStep(step) {
    this.setState({
      currentStep: step
    })
  }

  renderStepTitle() {
    switch(this.state.currentStep) {
      case constants.RESOURCE_CREATION_TYPE:
        return trans('new_resource_select', {}, 'resource')
      case constants.RESOURCE_CREATION_PARAMETERS:
        return trans('new_resource_configure', {}, 'resource')
      case constants.RESOURCE_CREATION_RIGHTS:
        return trans('new_resource_configure_rights', {}, 'resource')
    }
  }

  renderStep() {
    switch(this.state.currentStep) {
      case constants.RESOURCE_CREATION_TYPE:
        return (
          <ResourceType
            types={this.props.parent.permissions.create}
            select={(type) => {
              this.props.startCreation(this.props.parent, type)
              this.changeStep(constants.RESOURCE_CREATION_PARAMETERS)
            }}
          />
        )
      case constants.RESOURCE_CREATION_PARAMETERS:
        return (
          <ResourceParameters
            resourceNode={this.props.newNode}
          />
        )
      case constants.RESOURCE_CREATION_RIGHTS:
        return (
          <ResourceRights
            resourceNode={this.props.newNode}
            updateRights={this.props.updateRights}
          />
        )
    }
  }

  close() {
    this.props.fadeModal()
    this.changeStep('type')
    this.props.reset()
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'parent', 'newNode', 'saveEnabled', 'startCreation', 'updateRights', 'save', 'reset', 'add')}
        icon="fa fa-fw fa-plus"
        title={trans('new_resource', {}, 'resource')}
        subtitle={this.renderStepTitle()}
        fadeModal={() => this.close()}
      >
        {this.renderStep()}

        {constants.RESOURCE_CREATION_PARAMETERS === this.state.currentStep &&
          <Button
            className="modal-btn btn-link"
            type={CALLBACK_BUTTON}
            label={trans('edit-rights', {}, 'actions')}
            disabled={!this.props.saveEnabled}
            callback={() => this.changeStep(constants.RESOURCE_CREATION_RIGHTS)}
          />
        }

        {constants.RESOURCE_CREATION_RIGHTS === this.state.currentStep &&
          <Button
            className="modal-btn btn-link"
            type={CALLBACK_BUTTON}
            label={trans('configure', {}, 'actions')}
            callback={() => this.changeStep(constants.RESOURCE_CREATION_PARAMETERS)}
          />
        }

        {constants.RESOURCE_CREATION_TYPE !== this.state.currentStep &&
          <Button
            className="modal-btn btn"
            type={CALLBACK_BUTTON}
            primary={true}
            label={trans('create', {}, 'actions')}
            disabled={!this.props.saveEnabled}
            callback={() => this.props.save(this.props.parent, () => {
              this.props.add(this.props.newNode)
              this.close()
            })}
          />
        }
      </Modal>
    )
  }
}

ResourceCreationModal.propTypes = {
  parent: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  add: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from redux store
  updateRights: T.func.isRequired,
  startCreation: T.func.isRequired,
  save: T.func.isRequired,
  reset: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  newNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  ResourceCreationModal
}