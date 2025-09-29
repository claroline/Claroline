import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import moment from 'moment/moment'
import times from 'lodash/times'

import {trans, displayDate} from '#/main/app/intl'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {Html} from '#/main/app/components/html'

function getDayClass(activityCount = 0) {
  if (0 === activityCount) {
    return 'activity-calendar-day-0'
  }

  if (0 < activityCount && 1 >= activityCount) {
    return 'activity-calendar-day-1'
  }

  if (1 < activityCount && 5 >= activityCount) {
    return 'activity-calendar-day-2'
  }

  if (5 < activityCount && 10 >= activityCount) {
    return 'activity-calendar-day-3'
  }

  if (10 < activityCount) {
    return 'activity-calendar-day-4'
  }
}

const ActivityCalendar = ({className, label, data = {}}) => {
  const endRange = moment()
  const startRange = moment().subtract(52, 'week')

  const diff = moment.duration(endRange.diff(startRange))

  return (
    <div className={classes('activity-calendar-container', className)}>
      <table className="activity-calendar">
        <thead>
          <tr>
            <th scope="col">
              <span className="visually-hidden">{trans('days')}</span>
            </th>
            {times(12, (monthNum) => {
              const date = moment(startRange).add(monthNum, 'month').startOf('month')
              const next = moment(date).endOf('month')
              const weeks = 1 === next.week() ? 53 - date.week() : next.week() - date.week()

              return (
                <th key={monthNum} scope="col" colSpan={weeks}>{moment.monthsShort(date.month())}</th>
              )
            })}
          </tr>
        </thead>

        <tbody>
          {times(7, (dayNum) =>
            <tr key={dayNum}>
              <th className="activity-calendar-day-label" scope="row">{moment.weekdaysShort(dayNum)}</th>

              {times(diff.asWeeks() + 1, (week) => {
                const date = moment(startRange).add(week, 'week').day(dayNum)
                const count = get(data, date.format('YYYY-M-D'), 0)

                return (
                  <td key={week}>
                    {date <= endRange &&
                      <TooltipOverlay tip={<Html>{label(displayDate(date, true, false), count)}</Html>}>
                        <div className={classes('activity-calendar-day', getDayClass(count))} />
                      </TooltipOverlay>
                    }
                  </td>
                )
              })}
            </tr>
          )}
        </tbody>
      </table>

      <div className="activity-calendar-legend">
        <span className="">{trans('Moins')}</span>

        <span className="activity-calendar-day-legend activity-calendar-day activity-calendar-day-0">
          <span className="visually-hidden">{trans('No activity')}</span>
        </span>

        <span className="activity-calendar-day-legend activity-calendar-day activity-calendar-day-1">
          <span className="visually-hidden">{trans('Low activity')}</span>
        </span>

        <span className="activity-calendar-day-legend activity-calendar-day activity-calendar-day-2">
          <span className="visually-hidden">{trans('Medium-low activity')}</span>
        </span>

        <span className="activity-calendar-day-legend activity-calendar-day activity-calendar-day-3">
          <span className="visually-hidden">{trans('Medium-high activity')}</span>
        </span>

        <span className="activity-calendar-day-legend activity-calendar-day activity-calendar-day-4">
          <span className="visually-hidden">{trans('High activity')}</span>
        </span>

        <span className="">{trans('Plus')}</span>
      </div>
    </div>
  )
}

ActivityCalendar.propTypes = {
  className: T.string,
  data: T.object,
  label: T.func
}

export {
  ActivityCalendar
}
