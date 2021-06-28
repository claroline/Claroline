import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ContentLoader} from '#/main/app/content/components/loader'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {getEvent} from '#/plugin/agenda/events'

class AgendaEvent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      customDetails: null
    }
  }

  componentDidMount() {
    if (this.props.event) {
      getEvent(this.props.event.meta.type).then((eventApp) => {
        this.setState({customDetails: get(eventApp, 'components.details', null)})
      })
    }
  }

  componentDidUpdate(prevProps) {
    if (this.props.event && (!prevProps.event || this.props.event.id !== prevProps.event.id)) {
      getEvent(this.props.event.meta.type).then((eventApp) => {
        this.setState({customDetails: get(eventApp, 'components.details', null)})
      })
    }
  }

  render() {
    if (!this.props.event) {
      return (
        <ContentLoader
          size="lg"
          description="Nous chargeons votre évènement..."
        />
      )
    }

    if (this.state.customDetails) {
      return createElement(this.state.customDetails, {
        path: this.props.path + '/event',
        event: this.props.event,
        reload: this.props.reload
      })
    }

    return null
  }
}


AgendaEvent.propTypes = {
  path: T.string.isRequired,
  event: T.shape(
    EventTypes.propTypes
  ),
  reload: T.func.isRequired
}

export {
  AgendaEvent
}
