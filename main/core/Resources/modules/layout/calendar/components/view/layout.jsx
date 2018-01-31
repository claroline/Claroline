import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Button} from '#/main/core/layout/button/components/button.jsx'

import {getNextView} from '#/main/core/layout/calendar/utils'

const CalendarNav = props =>
  <div className="calendar-nav">
    <Button
      className="btn-link-default calendar-previous"
      disabled={props.previousRange[1].isSameOrBefore(props.calendarRange[0])}
      onClick={() => props.changeView(props.view, props.previousRange)}
    >
      <span className="fa fa-chevron-left" />
    </Button>

    <Button
      className="btn-link-default calendar-current"
      onClick={() => props.changeView(getNextView(props.view))}
    >
      {props.title}
    </Button>

    <Button
      className="btn-link-default calendar-next"
      disabled={props.nextRange[0].isSameOrAfter(props.calendarRange[1])}
      onClick={() => props.changeView(props.view, props.nextRange)}
    >
      <span className="fa fa-chevron-right" />
    </Button>
  </div>

CalendarNav.propTypes = {
  title: T.string.isRequired,
  view: T.string.isRequired,
  changeView: T.func.isRequired,
  calendarRange: T.arrayOf(
    T.object
  ).isRequired,
  previousRange: T.arrayOf(
    T.object.isRequired
  ).isRequired,
  nextRange: T.arrayOf(
    T.object.isRequired
  ).isRequired
}

const CalendarLayout = props =>
  <div className="calendar">
    <CalendarNav
      title={props.title}
      view={props.view}
      changeView={props.changeView}
      calendarRange={props.calendarRange}
      previousRange={props.previousRange}
      nextRange={props.nextRange}
    />

    <div className={classes('calendar-grid', `calendar-${props.view}`)}>
      {props.children}
    </div>
  </div>

CalendarLayout.propTypes = {
  /**
   * The current calendar view.
   */
  view: T.string.isRequired,

  /**
   * The title for the displayed range.
   */
  title: T.string.isRequired,

  /**
   * The calendar date boundaries
   */
  calendarRange: T.arrayOf(
    T.object
  ).isRequired,

  /**
   * The previous displayable range.
   */
  previousRange: T.arrayOf(
    T.object
  ).isRequired,

  /**
   * The next displayable range.
   */
  nextRange: T.arrayOf(
    T.object
  ).isRequired,

  /**
   * Updates the calendar view and/or displayed range.
   */
  changeView: T.func.isRequired,
  children: T.node.isRequired
}

export {
  CalendarLayout
}
