import React, {Component} from 'react'
import moment from 'moment'
import padStart from 'lodash/padStart'

import {trans} from '#/main/core/translation'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {isValidDate, getApiFormat} from '#/main/core/scaffolding/date'
import {Button} from '#/main/core/layout/button/components/button.jsx'

import {Calendar as CalendarTypes} from '#/main/core/layout/calendar/prop-types'
import {constants} from '#/main/core/layout/calendar/constants'
import {calculateTime} from '#/main/core/layout/calendar/utils'

import {Days} from '#/main/core/layout/calendar/components/view/days.jsx'
import {Months} from '#/main/core/layout/calendar/components/view/months.jsx'
import {Years} from '#/main/core/layout/calendar/components/view/years.jsx'

const TimeInput = props =>
  <div className="time-input">
    <Button
      className="btn-sm btn-link-default"
      disabled={props.max === props.value}
      onClick={() => {
        props.onChange(calculateTime(props.value + props.step, props.max))
      }}
    >
      <span className="fa fa-fw fa-caret-up" />
    </Button>

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

    <Button
      className="btn-sm btn-link-default"
      disabled={0 === props.value}
      onClick={() => {
        props.onChange(calculateTime(props.value - props.step, props.max))
      }}
    >
      <span className="fa fa-fw fa-caret-down" />
    </Button>
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

    <Button
      className="btn-block btn-sm btn-now"
      onClick={props.today}
    >
      {trans(props.time ? 'now': 'today')}
    </Button>
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

    const now = moment()
      .utc()
      .set('second', 0)

    let selected
    if (this.props.selected && isValidDate(this.props.selected, getApiFormat())) {
      selected = moment.utc(this.props.selected)
    }

    // get the date which will serve for calculating current displayed range
    // we focus on the selected date, or now if no selected
    const referenceDate = selected ? selected : now

    this.state = {
      view: constants.CALENDAR_VIEW_DAYS,

      // create moment objects for all used dates
      now: now.local(),
      selected: selected ? selected.local() : null,
      currentRange: [
        moment(referenceDate).startOf('month'),
        moment(referenceDate).endOf('month')
      ],
      calendarRange: [
        moment(this.props.minDate),
        moment(this.props.maxDate)
      ],
      timeRange: [
        moment(this.props.minTime),
        moment(this.props.maxTime)
      ]
    }

    this.changeView = this.changeView.bind(this)
    this.update = this.update.bind(this)
    this.today = this.today.bind(this)
  }

  /*componentWillReceiveProps(nextProps) {
    // updates state if selected value changes at runtime
    if (this.props.selected !== nextProps.selected) {
      let selected = null
      if (this.props.selected && isValidDate(this.props.selected, getApiFormat())) {
        selected = moment.utc(this.props.selected).local()
      }

      this.setState({
        selected: selected
      })
    }
  }*/

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
    this.onChange(moment())
    this.changeView(constants.CALENDAR_VIEW_DAYS)
  }

  onChange(newDate) {
    this.setState({
      selected: newDate
    })

    if (this.props.onChange) {
      this.props.onChange(newDate.utc().format('YYYY-MM-DD\THH:mm:ss'))
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
      <div className="calendar-container">
        <CurrentDate
          selected={this.state.selected || this.state.now}
          time={this.props.time}
          timeRange={this.state.timeRange}
          update={this.update}
          today={this.today}
        />

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
