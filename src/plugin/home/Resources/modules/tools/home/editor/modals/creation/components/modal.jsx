import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {GridSelection} from '#/main/app/content/grid/components/selection'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
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
          <GridSelection
            items={this.state.tabs
              .map(tab => {
                return ({
                  name: tab.name,
                  icon: tab.icon,
                  label: trans(tab.name, {}, 'home'),
                  description: trans(`${tab.name}_desc`, {}, 'home')
                })
              })
            }
            handleSelect={(selectedTab) => {
              const newTab = this.state.tabs.find(tab => tab.name === selectedTab.name)

              this.props.startCreation(this.props.currentContext, newTab, this.props.administration, this.props.currentUser, this.props.position)
              this.changeStep('parameters')
            }}
          />
        )

      case 'parameters':
        return (
          <TabForm
            level={5}
            name={selectors.STORE_NAME}
            update={this.props.update}
            setErrors={this.props.setErrors}

            currentTab={this.props.tab}
            currentContext={this.props.currentContext}
            administration={this.props.administration}
          >
            <Button
              className="modal-btn btn"
              type={CALLBACK_BUTTON}
              primary={true}
              disabled={!this.props.saveEnabled}
              label={trans('add', {}, 'actions')}
              htmlType="submit"
              callback={() => {
                this.props.create(this.props.tab)
                this.close()
              }}
            />
          </TabForm>
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
        {...omit(this.props, 'currentUser', 'currentContext', 'administration', 'position', 'tab', 'saveEnabled', 'update', 'setErrors', 'create', 'startCreation', 'reset')}
        icon="fa fa-fw fa-plus"
        title={trans('new_tab', {}, 'home')}
        subtitle={this.renderStepTitle()}
        fadeModal={() => this.close()}
      >
        {this.renderStep()}
      </Modal>
    )
  }
}

TabCreationModal.propTypes = {
  currentUser: T.object,
  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }).isRequired,
  administration: T.bool,
  position: T.number,
  tab: T.shape(
    TabTypes.propTypes
  ),
  saveEnabled: T.bool.isRequired,
  create: T.func.isRequired,
  startCreation: T.func.isRequired,
  update: T.func.isRequired,
  setErrors: T.func.isRequired,
  reset: T.func,
  fadeModal: T.func.isRequired
}

export {
  TabCreationModal
}
