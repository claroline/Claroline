import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {WidgetForm} from '#/main/core/widget/editor/components/form'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'

import {selectors} from '#/main/core/widget/editor/modals/creation/store'
import {WidgetLayout} from '#/main/core/widget/editor/modals/creation/components/layout'

class WidgetCreationModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentStep: 'layout'
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
      case 'layout':
        return trans('new_section_select', {}, 'widget')
      case 'parameters':
        return trans('new_section_configure', {}, 'widget')
    }
  }

  renderStep() {
    switch(this.state.currentStep) {
      case 'layout':
        return (
          <WidgetLayout
            selectLayout={(layout) => {
              this.props.startCreation(layout)
              this.changeStep('parameters')
            }}
          />
        )
      case 'parameters':
        return (
          <WidgetForm
            level={5}
            name={selectors.STORE_NAME}
          />
        )
    }
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'widget', 'saveEnabled', 'startCreation', 'create')}
        icon="fa fa-fw fa-plus"
        title={trans('new_section')}
        subtitle={this.renderStepTitle()}
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
              this.props.create(this.props.widget)
              this.props.fadeModal()
            }}
          />
        }
      </Modal>
    )
  }
}

WidgetCreationModal.propTypes = {
  create: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from redux store
  widget: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  startCreation: T.func.isRequired,
  saveEnabled: T.bool.isRequired
}

export {
  WidgetCreationModal
}