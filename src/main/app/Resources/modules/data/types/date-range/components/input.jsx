import React, {Component} from 'react'
import classes from 'classnames'
import get from 'lodash/get'
import isArray from 'lodash/isArray'

import {trans} from '#/main/app/intl'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {DateInput} from '#/main/app/data/types/date/components/input'

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
      <div className={classes('row', this.props.className)}>
        <FormGroup
          id={`${this.props.id}-start`}
          className="col-md-6 col-xs-12"
          hideLabel={true}
          label={trans('range_start')}
          validating={this.props.validating}
          error={isArray(this.props.error) && get(this.props, 'error[0]') ? get(this.props, 'error[0]') : null}
        >
          <DateInput
            id={`${this.props.id}-start`}
            calendarIcon="fa fa-fw fa-calendar-check"
            value={this.props.value[0]}
            disabled={this.props.disabled}
            onChange={this.setRangeStart}
            minDate={this.props.minDate}
            maxDate={this.props.value[1] || this.props.maxDate}
            time={this.props.time}
            minTime={this.props.minTime}
            maxTime={this.props.maxTime}
          />
        </FormGroup>

        <FormGroup
          id={`${this.props.id}-end`}
          className="col-md-6 col-xs-12"
          hideLabel={true}
          label={trans('range_end')}
          validating={this.props.validating}
          error={isArray(this.props.error) && get(this.props, 'error[1]') ? get(this.props, 'error[1]') : null}
        >
          <DateInput
            id={`${this.props.id}-end`}
            calendarIcon="fa fa-fw fa-calendar-xmark"
            value={this.props.value[1]}
            disabled={this.props.disabled}
            onChange={this.setRangeEnd}
            minDate={this.props.value[0] || this.props.minDate}
            maxDate={this.props.maxDate}
            time={this.props.time}
            minTime={this.props.minTime}
            maxTime={this.props.maxTime}
          />
        </FormGroup>
      </div>
    )
  }
}

implementPropTypes(DateRangeInput, DataInputTypes, {
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
  value: [null, null],
  time: false
})

export {
  DateRangeInput
}
