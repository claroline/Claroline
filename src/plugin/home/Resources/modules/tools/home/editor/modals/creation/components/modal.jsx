import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentMenu} from '#/main/app/content/components/menu'

import {getTabs} from '#/plugin/home/home'
import {TabForm} from '#/plugin/home/tools/home/editor/components/form'
import {selectors} from '#/plugin/home/tools/home/editor/modals/creation/store'

class TabCreationModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentStep: 'type',
      tabs: [],
      loaded: false
    }

    this.changeStep = this.changeStep.bind(this)
  }

  componentDidMount() {
    getTabs(this.props.currentContext.type).then((tabs) => this.setState({
      tabs: tabs,
      loaded: true
    }))
  }

  changeStep(step) {
    this.setState({
      currentStep: step
    })
  }

  renderStepTitle() {
    switch (this.state.currentStep) {
      case 'type':
        return trans('new_tab_select', {}, 'home')
      case 'parameters':
        return trans('new_tab_configure', {}, 'home')
    }
  }

  renderStep() {
    switch (this.state.currentStep) {
      case 'type':
        return this.state.loaded && (
          <div className="modal-body" role="presentation">
            <ContentMenu
              className="mb-3"
              items={this.state.tabs.map(tab => ({
                id: tab.name,
                icon: tab.icon,
                label: trans(tab.name, {}, 'home'),
                description: trans(`${tab.name}_desc`, {}, 'home'),
                action: {
                  type: CALLBACK_BUTTON,
                  callback: () => {
                    this.props.startCreation(tab, this.props.position)
                    this.changeStep('parameters')
                  }
                }
              }))}
            />
          </div>
        )

      case 'parameters':
        return (
          <TabForm
            name={selectors.STORE_NAME}
            isNew={true}
            currentContext={this.props.currentContext}
            onSave={(formData) => {
              this.props.create(formData)
              this.props.fadeModal()
            }}
          >
            <Button
              type={CALLBACK_BUTTON}
              label={trans('back')}
              className="btn btn-text-body me-auto"
              callback={() => this.changeStep('type')}
            />
          </TabForm>
        )
    }
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'currentContext', 'position', 'create', 'startCreation', 'reset')}
        title={trans('new_page', {}, 'home')}
        subtitle={this.renderStepTitle()}
        onEnter={this.props.reset}
      >
        {this.renderStep()}
      </Modal>
    )
  }
}

TabCreationModal.propTypes = {
  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }).isRequired,
  position: T.number,
  create: T.func.isRequired,
  startCreation: T.func.isRequired,
  reset: T.func,
  fadeModal: T.func.isRequired
}

export {
  TabCreationModal
}
