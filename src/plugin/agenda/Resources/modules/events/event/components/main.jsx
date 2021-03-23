import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentLoader} from '#/main/app/content/components/loader'

class EventMain extends Component {
  constructor(props) {
    super(props)
  }

  componentDidMount() {
    this.props.open(this.props.eventId)
  }

  componentDidUpdate(prevProps) {
    if (prevProps.eventId !== this.props.eventId && this.props.eventId) {
      this.props.open(this.props.eventId)
    }
  }

  render() {
    if (!this.props.loaded) {
      return (
        <ContentLoader
          size="lg"
          description={trans('event_loading', {}, 'agenda')}
        />
      )
    }

    return this.props.children
  }
}

EventMain.propTypes = {
  eventId: T.string.isRequired,
  loaded: T.bool.isRequired,
  open: T.func.isRequired,
  children: T.node
}

export {
  EventMain
}
