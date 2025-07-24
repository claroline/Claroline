import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {WidgetForm} from '#/main/core/widget/editor/components/form'

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
            name={selectors.STORE_NAME}
            isNew={true}
            onSave={(formData) => {
              this.props.create(formData)
              this.props.fadeModal()
            }}
          >
            <Button
              type={CALLBACK_BUTTON}
              label={trans('back')}
              className="btn btn-text-body me-auto"
              callback={() => this.changeStep('layout')}
            />
          </WidgetForm>
        )
    }
  }

  render() {
    let subtitle
    switch(this.state.currentStep) {
      case 'layout':
        subtitle = trans('new_section_select', {}, 'widget')
        break;
      case 'parameters':
        subtitle = trans('new_section_configure', {}, 'widget')
        break;
    }

    return (
      <Modal
        {...omit(this.props, 'widget', 'saveEnabled', 'startCreation', 'create', 'reset')}
        title={trans('new_section')}
        subtitle={subtitle}
        onExited={this.props.reset}
      >
        {this.renderStep()}
      </Modal>
    )
  }
}

WidgetCreationModal.propTypes = {
  create: T.func.isRequired,
  fadeModal: T.func.isRequired,
  reset: T.func,
  startCreation: T.func.isRequired
}

export {
  WidgetCreationModal
}
