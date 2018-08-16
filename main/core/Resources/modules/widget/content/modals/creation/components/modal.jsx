import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {Widget as WidgetTypes} from '#/main/core/widget/prop-types'

import {ContentType} from '#/main/core/widget/content/modals/creation/components/type'
import {WidgetContentForm} from '#/main/core/widget/content/components/form'
import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'
import {selectors} from '#/main/core/widget/content/modals/creation/store'

class ContentCreationModal extends Component {
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
      case 'type':
        return trans('new_widget_select', {}, 'widget')
      case 'parameters':
        return trans('new_widget_configure', {}, 'widget')
    }
  }

  renderStep() {
    switch(this.state.currentStep) {
      case 'type':
        return (
          <ContentType
            availableTypes={this.props.availableTypes}
            select={(layout) => {
              this.props.startCreation(layout)
              this.changeStep('parameters')
            }}
          />
        )
      case 'parameters':
        return (
          <WidgetContentForm level={5} name={selectors.FORM_NAME} />
        )
    }
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'context', 'add', 'instance', 'saveEnabled', 'availableTypes', 'fetchContents', 'startCreation')}
        icon="fa fa-fw fa-plus"
        title={trans('new_widget')}
        subtitle={this.renderStepTitle()}
        onEntering={() => {
          if (0 === this.props.availableTypes.length) {
            this.props.fetchContents(this.props.context)
          }
        }}
      >
        {this.renderStep()}

        {'parameters' === this.state.currentStep &&
          <Button
            className="modal-btn btn"
            type={CALLBACK_BUTTON}
            primary={true}
            disabled={!this.props.saveEnabled}
            label={trans('add', {}, 'actions')}
            callback={() => {
              this.props.add(this.props.instance)
              this.props.fadeModal()
            }}
          />
        }
      </Modal>
    )
  }
}

ContentCreationModal.propTypes = {
  context: T.object.isRequired,
  fadeModal: T.func.isRequired,
  add: T.func.isRequired,

  // from redux store
  instance: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired,
  saveEnabled: T.bool.isRequired,
  availableTypes: T.arrayOf(T.shape(
    WidgetTypes.propTypes
  )).isRequired,
  fetchContents: T.func.isRequired,
  startCreation: T.func.isRequired
}

export {
  ContentCreationModal
}