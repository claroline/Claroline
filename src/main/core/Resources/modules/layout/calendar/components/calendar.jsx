import React, {Component} from 'react'
import classes from 'classnames'
import moment from 'moment'
import get from 'lodash/get'
import padStart from 'lodash/padStart'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {isValidDate, getApiFormat} from '#/main/app/intl/date'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {Calendar as CalendarTypes} from '#/main/core/layout/calendar/prop-types'
import {constants} from '#/main/core/layout/calendar/constants'
import {calculateTime} from '#/main/core/layout/calendar/utils'

import {Days} from '#/main/core/layout/calendar/components/view/days.jsx'
import {Months} from '#/main/core/layout/calendar/components/view/months.jsx'
import {Years} from '#/main/core/layout/calendar/components/view/years.jsx'

const TimeInput = props =>
  <div className="time-input">
    <CallbackButton
      className="btn btn-link btn-sm"
      disabled={props.max === props.value}
      callback={() => props.onChange(calculateTime((((props.value / props.step) | 0) + 1) * props.step, props.max))}
    >
      <span className="fa fa-fw fa-caret-up" />
    </CallbackButton>

    <input
      type="text"
      value={padStart(props.value, 2, '0')}
      onChange={e => {
        const value = parseInt(e.target.value)
        if (!isNaN(value) && isFinite(value)) {
          props.onChange(calculateTime(value, props.max))
        }
      }}
    />

    <CallbackButton
      className="btn btn-link btn-sm"
      disabled={0 === props.value}
      callback={() => props.onChange(calculateTime((((props.value / props.step) | 0) - 1) * props.step, props.max))}
    >
      <span className="fa fa-fw fa-caret-down" />
    </CallbackButton>
  </div>

TimeInput.propTypes = {
  value: T.number.isRequired,
  max: T.number.isRequired,
  step: T.number.isRequired,
  onChange: T.func.isRequired
}

const CurrentTime = props =>
  <div className="current-time">
    <TimeInput
      value={props.selected.get('hour')}
      onChange={t => props.update({hour: t})}
      max={23}
      step={1}
    />
    :
    <TimeInput
      value={props.selected.get('minute')}
      onChange={t => props.update({minute: t})}
      max={59}
      step={5}
    />
  </div>

CurrentTime.propTypes = {
  selected: T.object.isRequired,
  timeRange: T.arrayOf(
    T.object
  ).isRequired,
  update: T.func.isRequired
}

const CurrentDate = props =>
  <div className="current-container">
    <h4 className="current-date">
      <small>{props.selected.format('dddd')}</small>
      {props.selected.format('ll')}
    </h4>

    {props.time &&
      <CurrentTime
        selected={props.selected}
        timeRange={props.timeRange}
        update={props.update}
      />
    }

    <CallbackButton
      className="btn-link btn-now btn-block"
      callback={props.today}
      size="sm"
    >
      {trans(props.time ? 'now': 'today')}
    </CallbackButton>
  </div>

CurrentDate.propTypes = {
  selected: T.object.isRequired,
  time: T.bool.isRequired,
  timeRange: T.arrayOf(
    T.object
  ).isRequired,
  update: T.func.isRequired,
  today: T.func.isRequired
}

/**
 * Renders a mini calendar with date & time selection.
 *
 * @todo implements timeRange.
 * @todo manages currentRange when now (maybe selected too) is not in calendarRange.
 * @todo rounds now minutes to minutes step (currently 5)
 */
class Calendar extends Component {
  constructor(props) {
    super(props)

    this.state = this.init()

    this.changeView = this.changeView.bind(this)
    this.update = this.update.bind(this)
    this.today = this.today.bind(this)
  }

  componentDidUpdate(prevProps) {
    const triggerRefresh  = [
      'selected',
      'minDate',
      'maxDate',
      'minTime',
      'maxTime'
    ]

    if (-1 !== triggerRefresh.findIndex(prop => prevProps[prop] !== this.props[prop])) {
      this.setState(this.init())
    }
  }

  init() {
    // Get local current time as UTC current time
    const now = moment().set('second', 0)

    let selected
    if (this.props.selected && isValidDate(this.props.selected, getApiFormat())) {
      selected = moment.utc(this.props.selected).local()
    }

    // get the date which will serve for calculating current displayed range
    // we focus on the selected date, or now if no selected
    const referenceDate = selected ? selected : now

    return {
      view: get(this.state, 'view') || constants.CALENDAR_VIEW_DAYS,

      // create moment objects for all used dates
      now: now,
      selected: selected ? selected : null,
      currentRange: [
        moment(referenceDate).startOf('month'),
        moment(referenceDate).endOf('month')
      ],
      calendarRange: [
        moment(this.props.minDate),
        moment(this.props.maxDate)
      ],
      timeRange: [
        moment.utc(this.props.minTime, 'HH:mm'),
        moment.utc(this.props.maxTime, 'HH:mm')
      ]
    }
  }

  /**
   * Updates the selected date.
   *
   * @param {object} parts - the date parts to update (day, month, year, etc.).
   */
  update(parts) {
    // clone moment object (all setters mutate moment object)
    const newDate = this.state.selected ?
      moment(this.state.selected) : moment(this.state.now)

    newDate.set(parts)

    this.onChange(newDate)
  }

  today() {
    this.onChange(this.state.now)
    this.changeView(constants.CALENDAR_VIEW_DAYS)
  }

  onChange(newDate) {
    this.setState({
      selected: newDate
    })

    if (this.props.onChange) {
      const date = newDate.utc()
      if (!this.props.time) {
        date.hours(0).minutes(0).seconds(0)
      }

      this.props.onChange(date.format(getApiFormat()))
    }
  }

  /**
   * Changes the calendar view and range.
   */
  changeView(view, range) {
    this.setState({
      view: view,
      currentRange: range || this.state.currentRange
    })
  }

  /**
   * Renders the current calendar view.
   *
   * @param props
   *
   * @return {object}
   */
  renderView(props) {
    switch (this.state.view) {
      case constants.CALENDAR_VIEW_YEARS:
        return (<Years {...props} />)
      case constants.CALENDAR_VIEW_MONTHS:
        return (<Months {...props} />)
      case constants.CALENDAR_VIEW_DAYS:
      default:
        return (<Days {...props} />)
    }
  }

  render() {
    return (
      <div className={classes('calendar-container', {
        light: this.props.light,
        dark: !this.props.light,
        vertical: this.props.vertical
      })}>
        {this.props.showCurrent &&
          <CurrentDate
            selected={this.state.selected || this.state.now}
            time={this.props.time}
            timeRange={this.state.timeRange}
            update={this.update}
            today={this.today}
          />
        }

        {this.renderView({
          now: this.state.now,
          selected: this.state.selected,
          calendarRange: this.state.calendarRange,
          currentRange: this.state.currentRange,
          changeView: this.changeView,
          update: this.update
        })}
      </div>
    )
  }
}

implementPropTypes(Calendar, CalendarTypes)

export {
  Calendar
}
