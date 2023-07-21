import React, {Component} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {isValidDate, getApiFormat, getDisplayFormat, displayDate, apiDate} from '#/main/app/intl/date'

import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {CalendarMenu} from '#/main/app/data/types/date/components/menu'

class DateInput extends Component {
  constructor(props) {
    super(props)

    this.onInputChange = this.onInputChange.bind(this)
  }

  onInputChange(e) {
    // strict parsing to avoid catching too many things
    // (ex. a simple int like 10 is a valid date for moment)
    if (isValidDate(e.target.value, getDisplayFormat(false, this.props.time))) {
      this.props.onChange(apiDate(e.target.value, false, this.props.time))
    } else {
      this.props.onChange(e.target.value)
    }
  }

  render() {
    const displayFormat = getDisplayFormat(false, this.props.time)

    let displayValue = this.props.value || ''
    if (this.props.value && isValidDate(this.props.value, getApiFormat())) {
      displayValue = displayDate(this.props.value, false, this.props.time)
    }

    return (
      <div className={classes('date-control input-group', this.props.className, {
        [`input-group-${this.props.size}`]: !!this.props.size
      })}>
        <Button
          className="btn btn-outline-secondary"
          type={MENU_BUTTON}
          icon={this.props.calendarIcon}
          label={trans('show-calendar', {}, 'actions')}
          tooltip="right"
          size={this.props.size}
          disabled={this.props.disabled}
          menu={
            <CalendarMenu
              value={this.props.value}
              onChange={this.props.onChange}
              minDate={this.props.minDate}
              maxDate={this.props.maxDate}
              time={this.props.time}
              minTime={this.props.minTime}
              maxTime={this.props.maxTime}
            />
          }
        />

        <input
          id={this.props.id}
          type="text"
          autoComplete={this.props.autoComplete || 'date'}
          className="form-control"
          placeholder={this.props.placeholder || displayFormat}
          value={displayValue}
          disabled={this.props.disabled}
          onChange={this.onInputChange}
        />
      </div>
    )
  }
}

implementPropTypes(DateInput, DataInputTypes, {
  value: T.string,
  calendarIcon: T.string,

  // date configuration
  minDate: T.string,
  maxDate: T.string,

  // time configuration
  time: T.bool,
  minTime: T.string,
  maxTime: T.string
}, {
  value: '',
  time: false,
  calendarIcon: 'fa fa fa-fw fa-calendar'
})

export {
  DateInput
}
