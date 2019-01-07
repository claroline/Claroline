import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group.jsx'
import {DateGroup} from '#/main/core/layout/form/components/group/date-group.jsx'

// todo : fix responsive (incorrect margin bottom)
// todo : manages errors

const DateRangeGroup = props =>
  <FormGroup
    {...props}
    id={`${props.id}-start`}
  >
    <div className="row">
      <div className="col-md-6 col-xs-12">
        <DateGroup
          id={`${props.id}-start`}
          className="form-last"
          calendarIcon="fa fa fa-fw fa-calendar-check-o"
          label={trans('date_range_start')}
          hideLabel={true}
          value={props.value[0]}
          disabled={props.disabled}
          onChange={(date) => props.onChange([date, props.value[1]])}
          minDate={props.minDate}
          maxDate={props.value[1] || props.maxDate}
          time={props.time}
          minTime={props.minTime}
          maxTime={props.maxTime}
        />
      </div>

      <div className="col-md-6 col-xs-12">
        <DateGroup
          id={`${props.id}-end`}
          className="form-last"
          calendarIcon="fa fa fa-fw fa-calendar-times-o"
          label={trans('date_range_end')}
          hideLabel={true}
          value={props.value[1]}
          disabled={props.disabled}
          onChange={(date) => props.onChange([props.value[0], date])}
          minDate={props.value[0] || props.minDate}
          maxDate={props.maxDate}
          time={props.time}
          minTime={props.minTime}
          maxTime={props.maxTime}
        />
      </div>
    </div>
  </FormGroup>

implementPropTypes(DateRangeGroup, FormGroupWithFieldTypes, {
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
  DateRangeGroup
}
