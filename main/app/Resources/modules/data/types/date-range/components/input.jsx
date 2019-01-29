import React, {Component} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {DateInput} from '#/main/app/data/types/date/components/input'

// todo : fix responsive (incorrect margin bottom)
// todo : manages errors

class DateRangeInput extends Component {
  constructor(props) {
    super(props)

    this.setRangeStart = this.setRangeStart.bind(this)
    this.setRangeEnd = this.setRangeEnd.bind(this)
  }

  setRangeStart(date) {
    this.props.onChange([date, this.props.value[1]])
  }

  setRangeEnd(date) {
    this.props.onChange([this.props.value[0], date])
  }

  render() {
    return (
      <div className="row">
        <div className="col-md-6 col-xs-12">
          <DateInput
            id={`${this.props.id}-start`}
            calendarIcon="fa fa-fw fa-calendar-check-o"
            value={this.props.value[0]}
            disabled={this.props.disabled}
            onChange={this.setRangeStart}
            minDate={this.props.minDate}
            maxDate={this.props.value[1] || this.props.maxDate}
            time={this.props.time}
            minTime={this.props.minTime}
            maxTime={this.props.maxTime}
          />
        </div>

        <div className="col-md-6 col-xs-12">
          <DateInput
            id={`${this.props.id}-end`}
            calendarIcon="fa fa-fw fa-calendar-times-o"
            value={this.props.value[1]}
            disabled={this.props.disabled}
            onChange={this.setRangeEnd}
            minDate={this.props.value[0] || this.props.minDate}
            maxDate={this.props.maxDate}
            time={this.props.time}
            minTime={this.props.minTime}
            maxTime={this.props.maxTime}
          />
        </div>
      </div>
    )
  }
}

implementPropTypes(DateRangeInput, FormFieldTypes, {
  // more precise value type
  value: T.arrayOf(T.string),

  // date configuration
  minDate: T.string,
  maxDate: T.string,

  // time configuration
  time: T.bool,
  minTime: T.string,
  maxTime: T.string
}, {
  value: [null, null]
})

export {
  DateRangeInput
}
