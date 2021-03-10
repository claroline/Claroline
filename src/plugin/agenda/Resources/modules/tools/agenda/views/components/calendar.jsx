import {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'

import {withRouter} from '#/main/app/router'

import {AGENDA_VIEWS} from '#/plugin/agenda/tools/agenda/views'

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
    // load events list
    const reference = moment(this.props.referenceDate)
    const view = AGENDA_VIEWS[this.props.view]

    this.props.loadEvents(view.range(reference), force)
  }

  render() {
    return this.props.children
  }
}

CalendarViewComponent.propTYpes = {
  loaded: T.bool.isRequired,
  view: T.string.isRequired,
  referenceDate: T.object.isRequired,
  loadEvents: T.func.isRequired,
  children: T.node
}

const CalendarView = withRouter(CalendarViewComponent)

export {
  CalendarView
}
