import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {Calendar} from '#/main/core/layout/calendar/components/calendar'

const DateSearch = props =>
  <span className="data-filter date-filter">
    {props.isValid &&
      <span className="available-filter-value">{props.search}</span>
    }

    <Button
      className="btn btn-filter"
      type={MENU_BUTTON}
      icon={props.calendarIcon}
      label={trans('show-calendar', {}, 'actions')}
      tooltip="left"
      size="sm"
      disabled={props.disabled}
      menu={
        <div className="dropdown-menu dropdown-menu-right">
          <Calendar
            selected={props.isValid ? props.search : ''}
            onChange={props.updateSearch}
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

implementPropTypes(DateSearch, DataSearchTypes, {
  calendarIcon: T.string,

  // date configuration
  minDate: T.string,
  maxDate: T.string,

  // time configuration
  time: T.bool,
  minTime: T.string,
  maxTime: T.string
}, {
  calendarIcon: 'fa fa fa-fw fa-calendar'
})

export {
  DateSearch
}
