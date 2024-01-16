import React from 'react'
import isEmpty from 'lodash/isEmpty'
import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {CalendarMenu} from '#/main/app/data/types/date/components/menu'

const DateSearch = (props) =>
  <span className="data-filter date-filter">
    {!isEmpty(props.search) && props.isValid &&
      <span className="available-filter-value">{displayDate(props.search, false, props.time)}</span>
    }

    <Button
      className="btn btn-outline-secondary btn-filter"
      type={MENU_BUTTON}
      icon={props.calendarIcon}
      label={trans('show-calendar', {}, 'actions')}
      tooltip="left"
      size={props.size}
      disabled={props.disabled}
      menu={
        <CalendarMenu
          value={props.isValid ? props.search : ''}
          onChange={props.updateSearch}
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
