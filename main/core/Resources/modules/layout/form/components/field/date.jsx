import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {isValidDate, getApiFormat, getDisplayFormat, displayDate, apiDate} from '#/main/app/intl/date'

import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {Calendar} from '#/main/core/layout/calendar/components/calendar'

// deprecated
const Date = props => {
  const displayFormat = getDisplayFormat(false, props.time)

  let displayValue = props.value || ''
  if (props.value && isValidDate(props.value, getApiFormat())) {
    displayValue = displayDate(props.value, false, props.time)
  }

  return (
    <div className="input-group">
      <span className="input-group-btn">
        <Button
          className="btn"
          type={MENU_BUTTON}
          icon={props.calendarIcon}
          label={trans('show-calendar', {}, 'actions')}
          tooltip="right"
          size={props.size}
          disabled={props.disabled}
          menu={
            <div className="dropdown-menu">
              <Calendar
                selected={props.value}
                onChange={props.onChange}
                minDate={props.minDate}
                maxDate={props.maxDate}
                time={props.time}
                minTime={props.minTime}
                maxTime={props.maxTime}
              />
            </div>
          }
        />
      </span>

      <input
        id={props.id}
        type="text"
        autoComplete="date"
        className={classes('form-control', {[`input-${props.size}`]: !!props.size})}
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
  value: '',
  calendarIcon: 'fa fa fa-fw fa-calendar'
})

export {
  Date
}
