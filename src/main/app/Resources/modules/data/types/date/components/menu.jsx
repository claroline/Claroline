import React, {forwardRef} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Calendar} from '#/main/core/layout/calendar/components/calendar'
import {Menu} from '#/main/app/overlays/menu'

const CalendarDropdown = forwardRef((props, ref) =>
  <div {...omit(props, 'value', 'onChange', 'minDate', 'maxDate', 'time', 'minTime', 'maxTime', 'show', 'close')} ref={ref}>
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
)

CalendarDropdown.propTypes = {
  value: T.string,
  onChange: T.func.isRequired,
  minDate: T.string,
  maxDate: T.string,
  time: T.bool,
  minTime: T.string,
  maxTime: T.string
}

const CalendarMenu = (props) =>
  <Menu
    as={CalendarDropdown}
    value={props.value}
    onChange={props.onChange}
    minDate={props.minDate}
    maxDate={props.maxDate}
    time={props.time}
    minTime={props.minTime}
    maxTime={props.maxTime}
  />

CalendarMenu.propTypes = {
  value: T.string,
  onChange: T.func.isRequired,
  minDate: T.string,
  maxDate: T.string,
  time: T.bool,
  minTime: T.string,
  maxTime: T.string
}

export {
  CalendarMenu
}
