import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {GridSelection} from '#/main/app/content/grid/components/selection'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {getEvents} from '#/plugin/agenda/events'
import {EventParameters} from '#/plugin/agenda/event/components/parameters'
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
    this.close = this.close.bind(this)
  }

  componentDidMount() {
    getEvents().then((events) => this.setState({
      types: events.filter(event => {
        if (event.canCreate) {
          return event.canCreate(this.props.contextType, this.props.contextData, this.props.contextTools)
        }

        return true
      }),
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
              this.props.startCreation(this.props.event, selectedType.name, this.props.currentUser, this.props.contextData)
              this.changeStep('parameters')
            }}
          />
        )

      case 'parameters':
        return (
          <EventParameters
            name={selectors.STORE_NAME}
            event={this.props.formData}
            update={this.props.update}
            isNew={true}
            onSave={(response) => {
              this.props.onSave(response)
              this.close()
            }}
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
        {...omit(this.props, 'currentUser', 'contextData', 'contextType', 'contextTools', 'formData', 'event', 'saveEnabled', 'update', 'onSave', 'startCreation', 'reset')}
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
  contextType: T.string.isRequired,
  contextData: T.object,
  contextTools: T.arrayOf(T.object),
  event: T.shape(
    EventTypes.propTypes
  ),
  formData: T.shape(
    EventTypes.propTypes
  ),
  onSave: T.func,
  startCreation: T.func.isRequired,
  update: T.func.isRequired,
  reset: T.func,
  fadeModal: T.func.isRequired
}

export {
  EventCreationModal
}
