import {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {getEvent} from '#/plugin/agenda/events'
import {Event as EventTypes} from '#/plugin/agenda/prop-types'

class EventParameters extends Component {
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
        event: this.props.event,
        isNew: this.props.isNew,
        update: this.props.update,
        onSave: this.props.onSave
      })
    }

    return null
  }

  render() {
    return this.renderCustomForm()
  }
}

EventParameters.propTypes = {
  name: T.string.isRequired,
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  update: T.func.isRequired,
  isNew: T.bool,
  onSave: T.func,
  children: T.node
}

EventParameters.defaultProps = {
  isNew: false
}

export {
  EventParameters
}
