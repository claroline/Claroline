import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {getEvent} from '#/plugin/agenda/events'

class EventIcon extends Component {
  constructor(props) {
    super(props)

    this.state = {
      icon: null
    }
  }

  componentDidMount() {
    getEvent(this.props.type).then((eventApp) => {
      this.setState({icon: eventApp.icon || null})
    })
  }

  render() {
    if (this.state.icon) {
      return (
        <span className={classes(this.props.className, this.state.icon)} />
      )
    }

    return null
  }
}

EventIcon.propTypes = {
  className: T.string,
  type: T.string.isRequired
}

export {
  EventIcon
}
