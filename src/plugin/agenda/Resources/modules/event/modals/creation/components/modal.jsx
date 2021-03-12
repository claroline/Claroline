import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {GridSelection} from '#/main/app/content/grid/components/selection'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {getEvents} from '#/plugin/agenda/events'
import {EventForm} from '#/plugin/agenda/event/components/form'
import {selectors} from '#/plugin/agenda/event/modals/creation/store'

class EventCreationModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentStep: 'type',
      types: [],
      loaded: false
    }

    this.changeStep = this.changeStep.bind(this)
  }

  componentDidMount() {
    getEvents().then((events) => this.setState({
      types: events,
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
        return trans('new_event_select', {}, 'agenda')
      case 'parameters':
        return trans('new_event_configure', {}, 'agenda')
    }
  }

  renderStep() {
    switch (this.state.currentStep) {
      case 'type':
        return this.state.loaded && (
          <GridSelection
            items={this.state.types
              .map(event => {
                return ({
                  name: event.name,
                  icon: event.icon,
                  label: trans(event.name, {}, 'event'),
                  description: trans(`${event.name}_desc`, {}, 'event')
                })
              })
            }
            handleSelect={(selectedType) => {
              //const newType = this.state.types.find(type => type.name === selectedType.name)

              this.props.startCreation(this.props.event, selectedType.name, this.props.currentUser)
              this.changeStep('parameters')
            }}
          />
        )

      case 'parameters':
        return (
          <EventForm
            name={selectors.STORE_NAME}
            event={this.props.formData}
          >
            <Button
              className="modal-btn btn"
              type={CALLBACK_BUTTON}
              primary={true}
              disabled={!this.props.saveEnabled}
              label={trans('save', {}, 'actions')}
              htmlType="submit"
              callback={() => {
                this.props.create(this.props.formData)
                this.close()
              }}
            />
          </EventForm>
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
        {...omit(this.props, 'currentUser', 'currentContext', 'event', 'saveEnabled', 'update', 'create', 'startCreation', 'reset')}
        icon="fa fa-fw fa-plus"
        title={trans('new_event', {}, 'agenda')}
        subtitle={this.renderStepTitle()}
        fadeModal={() => this.close()}
      >
        {this.renderStep()}
      </Modal>
    )
  }
}

EventCreationModal.propTypes = {
  currentUser: T.object,
  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }).isRequired,
  event: T.shape(
    EventTypes.propTypes
  ),
  formData: T.shape(
    EventTypes.propTypes
  ),
  saveEnabled: T.bool.isRequired,
  create: T.func.isRequired,
  startCreation: T.func.isRequired,
  update: T.func.isRequired,
  reset: T.func,
  fadeModal: T.func.isRequired
}

export {
  EventCreationModal
}
