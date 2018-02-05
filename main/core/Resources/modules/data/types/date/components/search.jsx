import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/core/data/prop-types'

import {CalendarPicker} from '#/main/core/layout/calendar/components/picker.jsx'

const DateSearch = props =>
  <span className="date-filter">
    {props.isValid &&
      <span className="available-filter-value">{props.search}</span>
    }
    &nbsp;
    <CalendarPicker
      className="btn-sm btn-filter"
      selected={props.isValid ? props.search : ''}
      onChange={props.updateSearch}
      minDate={props.minDate}
      maxDate={props.maxDate}
      time={props.time}
      minTime={props.minTime}
      maxTime={props.maxTime}
    />
  </span>

implementPropTypes(DateSearch, DataSearchTypes, {
  // date configuration
  minDate: T.string,
  maxDate: T.string,

  // time configuration
  time: T.bool,
  minTime: T.string,
  maxTime: T.string
})

export {
  DateSearch
}
