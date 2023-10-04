import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {CalendarMenu} from '#/main/app/data/types/date/components/menu'

const DateSearch = (props) =>
  <span className="data-filter date-filter">
    {props.isValid &&
      <span className="available-filter-value">{props.search}</span>
    }

    <Button
      className="btn btn-outline-secondary btn-filter"
      type={MENU_BUTTON}
      icon={props.calendarIcon}
      label={trans('show-calendar', {}, 'actions')}
      tooltip="left"
      size="sm"
      disabled={props.disabled}
      menu={
        <CalendarMenu
          value={props.isValid ? props.search : ''}
          onChange={(value) => {
            // this is a little weird but the updateSearch will automatically parse() the value sent to it
            // so in the case of dates it expects a render value
            props.updateSearch(displayDate(value, false, props.time), true)
          }}
          minDate={props.minDate}
          maxDate={props.maxDate}
          time={props.time}
          minTime={props.minTime}
          maxTime={props.maxTime}
        />
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
