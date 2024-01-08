import React from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment/moment'
import times from 'lodash/times'
import random from 'lodash/random'

import {trans} from '#/main/app/intl/translation'
import classes from 'classnames'

const ActivityCalendar = (props) => {
  const endRange = moment()
  const startRange = moment().subtract(1, 'year')


  const diff = moment.duration(endRange.diff(startRange))

  return (
    <div className="activity-calendar-container">
      <table className="activity-calendar">
          <thead>
            <tr>
              <th scope="col">
                <span className="sr-only">{trans('days')}</span>
              </th>
              {times(12, (monthNum) => {
                //const date = moment(startRange).set('month', monthNum)
                return (
                  <th scope="col" colSpan={4}>{moment.monthsShort(monthNum)}</th>
                )
              })}
            </tr>
          </thead>

        <tbody>
          {times(7, (dayNum) =>
            <tr>
              <th className="activity-calendar-day-label" scope="row">{moment.weekdaysShort(dayNum)}</th>

              {times(diff.asWeeks(), (week) =>
                <td className={classes('activity-calendar-day', `activity-calendar-day-`+random(0, 4))}>
                </td>
              )}
            </tr>
          )}
        </tbody>
      </table>
      <div className="activity-calendar-legend">

      </div>
    </div>
  )
}

ActivityCalendar.propTypes = {

}

export {
  ActivityCalendar
}