import React, { Component } from 'react'

import cloneDeep from 'lodash/cloneDeep'
import $ from 'jquery'
import {PropTypes as T} from 'prop-types'
//import 'moment/min/moment.min.js'

import 'fullcalendar/dist/fullcalendar.css'
import 'fullcalendar/dist/fullcalendar.print.min.css'
import 'fullcalendar/dist/fullcalendar.js'

//fullcalendar wrapper
class Calendar extends Component {
  constructor(props) {
    super(props)
    this.calendarRef

    this.onEventDrop = this.onEventDrop.bind(this)
    this.onDayClick = this.onDayClick.bind(this)
    this.onEventClick = this.onEventClick.bind(this)
    this.onEventRender = this.onEventRender.bind(this)
    this.onEventResize = this.onEventResize.bind(this)
  }

  onEventDrop(event, delta, revertFunc, jsEvent, ui, view) {
    this.props.eventDrop($(this.calendarRef), event, delta, revertFunc, jsEvent, ui, view)
  }

  onDayClick(date) {
    this.props.dayClick($(this.calendarRef), this.props.workspace, date)
  }

  onEventClick(event) {
    this.props.eventClick($(this.calendarRef), event)
  }

  onEventRender(event, $element) {
    this.props.eventRender($(this.calendarRef), event, $element)
  }

  onEventResize(event, delta, revertFunc, jsEvent, ui, view) {
    this.props.eventResize($(this.calendarRef), event, delta, revertFunc, jsEvent, ui, view)
  }

  componentDidMount() {
    const calendarProps = cloneDeep(this.props)

    calendarProps.eventDrop = this.onEventDrop
    calendarProps.dayClick = this.onDayClick
    calendarProps.eventClick = this.onEventClick
    calendarProps.eventRender = this.onEventRender
    calendarProps.eventResize = this.onEventResize

    $(this.calendarRef).fullCalendar(calendarProps)
  }

  render() {
    return <div id="fullcalendar" className="col-md-9" ref={(el) => this.calendarRef = el}/>
  }
}

Calendar.propTypes = {
  eventDrop: T.func.isRequired,
  dayClick: T.func.isRequired,
  eventClick: T.func.isRequired,
  eventDestroy: T.func.isRequired,
  eventRender: T.func.isRequired,
  eventResize: T.func.isRequired,
  eventResizeStart: T.func.isRequired,
  workspace: T.object
}

export {
  Calendar
}
