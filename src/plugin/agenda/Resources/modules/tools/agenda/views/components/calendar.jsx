import {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {withRouter} from '#/main/app/router'

class CalendarViewComponent extends Component {
  componentDidMount() {
    if (!this.props.loaded) {
      this.loadEvents()
    }
  }

  componentDidUpdate(prevProps) {
    if (prevProps.loaded !== this.props.loaded && !this.props.loaded) {
      this.loadEvents()
    }
  }

  loadEvents(force = false) {
    this.props.loadEvents(this.props.range, force)
  }

  render() {
    return this.props.children
  }
}

CalendarViewComponent.propTYpes = {
  loaded: T.bool.isRequired,
  range: T.arrayOf(T.object),
  loadEvents: T.func.isRequired,
  children: T.node
}

const CalendarView = withRouter(CalendarViewComponent)

export {
  CalendarView
}
