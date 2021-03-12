import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {getEvent} from '#/plugin/agenda/events'
import {Event as EventTypes} from '#/plugin/agenda/prop-types'

class EventForm extends Component {
  constructor(props) {
    super(props)

    this.state = {
      customForm: null
    }
  }

  componentDidMount() {
    getEvent(this.props.event.meta.type).then((eventApp) => {
      this.setState({customForm: get(eventApp, 'components.form', null)})
    })
  }

  renderCustomForm() {
    if (this.state.customForm) {
      return createElement(this.state.customForm, {
        name: this.props.name,
        event: this.props.event
      })
    }

    return null
  }

  render() {
    return this.renderCustomForm()
  }
}


EventForm.propTypes = {
  name: T.string.isRequired,
  event: T.shape(
    EventTypes.propTypes
  ).isRequired
}

export {
  EventForm
}
