import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {isValidDate, getApiFormat, getDisplayFormat, displayDate, apiDate} from '#/main/core/scaffolding/date'

import {CalendarPicker} from '#/main/core/layout/calendar/components/picker.jsx'

const Date = props => {
  const displayFormat = getDisplayFormat(false, props.time)

  let displayValue = props.value || ''
  if (props.value && isValidDate(props.value, getApiFormat())) {
    displayValue = displayDate(props.value, false, props.time)
  }

  return (
    <div className="input-group">
      <span className="input-group-btn">
        <CalendarPicker
          className="btn-default"
          icon={props.calendarIcon}
          selected={props.value}
          disabled={props.disabled}
          onChange={props.onChange}
          minDate={props.minDate}
          maxDate={props.maxDate}
          time={props.time}
          minTime={props.minTime}
          maxTime={props.maxTime}
        />
      </span>

      <input
        id={props.id}
        type="text"
        autoComplete="date"
        className="form-control"
        placeholder={displayFormat}
        value={displayValue}
        disabled={props.disabled}
        onChange={(e) => {
          if (!props.disabled) {
            // strict parsing to avoid catching too many things
            // (ex. a simple int like 10 is a valid date for moment)
            if (isValidDate(e.target.value, displayFormat)) {
              props.onChange(apiDate(e.target.value, false, props.time))
            } else {
              props.onChange(e.target.value)
            }
          }
        }}
      />
    </div>
  )
}

implementPropTypes(Date, FormFieldTypes, {
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
  value: ''
})

export {
  Date
}
