import React from 'react'
import classes from 'classnames'
import moment from 'moment'
import times from 'lodash/times'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {CalendarView as CalendarViewTypes} from '#/main/core/layout/calendar/prop-types'
import {CalendarLayout} from '#/main/core/layout/calendar/components/view/layout'
import {constants} from '#/main/core/layout/calendar/constants'

const Day = props =>
  <CallbackButton
    type="callback"
    className={classes('btn btn-link day', {
      now:      props.current.isSame(props.now, 'day'),
      selected: props.selected && props.current.isSame(props.selected, 'day'),
      fill:     props.month !== props.current.get('month')
    })}
    disabled={props.current.isBefore(props.calendarRange[0]) || props.current.isAfter(props.calendarRange[1])}
    callback={() => {
      if (props.month !== props.current.get('month')) {
        // set calendar view to the correct month
        props.changeView(constants.CALENDAR_VIEW_DAYS, [
          moment(props.current).startOf('month'),
          moment(props.current).endOf('month')
        ])
      }

      // update value
      props.update({
        year:  props.current.get('year'),
        month: props.current.get('month'),
        date:  props.current.get('date')
      })
    }}
  >
    {props.current.format('D')}
  </CallbackButton>

Day.propTypes = {
  current: T.object.isRequired,
  month: T.number.isRequired,
  calendarRange: T.arrayOf(
    T.object
  ).isRequired,
  now: T.object.isRequired,
  selected: T.object,
  update: T.func.isRequired,
  changeView: T.func.isRequired
}

/**
 * Displays a grid with all days in a month (one row per week).
 * NB. The grid has 6 rows because some times a month can overflow on 6 weeks.
 *
 * @param props
 * @constructor
 */
const Days = props =>
  <CalendarLayout
    view={constants.CALENDAR_VIEW_DAYS}
    title={props.currentRange[0].format('MMMM YYYY')}
    changeView={props.changeView}
    calendarRange={props.calendarRange}
    previousRange={[
      moment(props.currentRange[0]).subtract(1, 'month'),
      moment(props.currentRange[1]).subtract(1, 'month')
    ]}
    nextRange={[
      moment(props.currentRange[0]).add(1, 'month'),
      moment(props.currentRange[1]).add(1, 'month')
    ]}
  >
    <table cellSpacing="0" cellPadding="0">
      <thead>
        <tr>
          <th scope="col">
            <span className="sr-only">week number</span>
          </th>

          {times(7, (dayNum) =>
            <th key={`day-${dayNum}`} scope="col" className="day-name">
              {moment().weekday(dayNum).format('ddd')}
            </th>
          )}
        </tr>
      </thead>

      <tbody>
        {times(6, (weekNum) =>
          <tr key={`week-${weekNum}`} className="calendar-row week">
            <th scope="row" className="week-num">
              {moment(props.currentRange[0])
                .week(props.currentRange[0].week()+weekNum).week()}
            </th>

            {times(7, (dayNum) =>
              <td key={`day-${weekNum}-${dayNum}`}>
                <Day
                  current={
                    moment(props.currentRange[0])
                      .week(props.currentRange[0].week()+weekNum)
                      .weekday(dayNum)
                  }
                  month={props.currentRange[0].get('month')}
                  calendarRange={props.calendarRange}
                  now={props.now}
                  selected={props.selected}
                  update={props.update}
                  changeView={props.changeView}
                />
              </td>
            )}
          </tr>
        )}
      </tbody>
    </table>
  </CalendarLayout>

implementPropTypes(Days, CalendarViewTypes)

export {
  Days
}
