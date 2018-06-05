import React from 'react'
import classes from 'classnames'
import times from 'lodash/times'
import moment from 'moment'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {CallbackButton} from '#/main/app/button/components/callback'

import {CalendarView as CalendarViewTypes} from '#/main/core/layout/calendar/prop-types'
import {CalendarLayout} from '#/main/core/layout/calendar/components/view/layout.jsx'
import {constants} from '#/main/core/layout/calendar/constants'
import {monthNum} from '#/main/core/layout/calendar/utils'

const Month = props =>
  <CallbackButton
    className={classes('btn btn-link month', {
      now:      props.current.isSame(props.now, 'month'),
      selected: props.selected && props.current.isSame(props.selected, 'month')
    })}
    disabled={props.current.isBefore(props.calendarRange[0], 'month') || props.current.isAfter(props.calendarRange[1], 'month')}
    callback={props.onClick}
  >
    {props.current.format('MMM')}
  </CallbackButton>

Month.propTypes = {
  calendarRange: T.arrayOf(
    T.object
  ).isRequired,
  current: T.object.isRequired,
  now: T.object.isRequired,
  selected: T.object,
  onClick: T.func.isRequired
}

/**
 * Displays a grid with the 12 months a of year (one row per quarter).
 *
 * @param props
 * @constructor
 */
const Months = props =>
  <CalendarLayout
    view={constants.CALENDAR_VIEW_MONTHS}
    title={props.currentRange[0].format('YYYY')}
    changeView={props.changeView}
    calendarRange={props.calendarRange}
    previousRange={[
      moment(props.currentRange[0]).subtract(1, 'year'),
      moment(props.currentRange[1]).subtract(1, 'year')
    ]}
    nextRange={[
      moment(props.currentRange[0]).add(1, 'year'),
      moment(props.currentRange[1]).add(1, 'year')
    ]}
  >
    {times(3, (quarterNum) =>
      <div key={`quarter-${quarterNum}`} className="calendar-row quarter">
        {times(4, (quarterMonthNum) =>
          <Month
            key={`month-${quarterNum}-${quarterMonthNum}`}
            calendarRange={props.calendarRange}
            current={moment(props.currentRange[0]).set('month', monthNum(quarterNum, quarterMonthNum))}
            now={props.now}
            selected={props.selected}
            onClick={() => props.changeView(constants.CALENDAR_VIEW_DAYS, [
              moment(props.currentRange[0]).set('month', monthNum(quarterNum, quarterMonthNum)),
              moment(props.currentRange[1]).set('month', monthNum(quarterNum, quarterMonthNum))
            ])}
          />
        )}
      </div>
    )}
  </CalendarLayout>

implementPropTypes(Months, CalendarViewTypes)

export {
  Months
}
