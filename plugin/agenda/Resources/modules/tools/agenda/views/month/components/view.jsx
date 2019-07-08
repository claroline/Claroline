import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import moment from 'moment'
import times from 'lodash/times'

import {LinkButton} from '#/main/app/buttons/link'
import {now} from '#/main/app/intl/date'

import {calendarUrl} from '#/plugin/agenda/tools/agenda/utils'

const Day = props =>
  <div className={classes('calendar-cell day', props.className)}>
    <LinkButton
      className="day-number"
      target={calendarUrl(props.path, 'day', props.current)}
    >
      {props.current.format('D')}
    </LinkButton>
  </div>

Day.propTypes = {
  path: T.string.isRequired,
  className: T.string,
  current: T.object.isRequired
}

const AgendaViewMonth = props => {
  const nowDate = moment(now())

  return (
    <div className="agenda-month">
      <div className="calendar-row day-names">
        {times(7, (dayNum) =>
          <div key={`day-${dayNum}`} className="calendar-cell day-name">
            {moment().weekday(dayNum).format('ddd')}
          </div>
        )}
      </div>

      {times(5, (weekNum) =>
        <div key={`week-${weekNum}`} className="calendar-row week">
          {times(7, (dayNum) => {
            const current = moment(props.range[0])
              .week(props.range[0].week()+weekNum)
              .weekday(dayNum)

            return (
              <Day
                key={`day-${weekNum}-${dayNum}`}
                path={props.path}
                className={classes({
                  now:      current.isSame(nowDate, 'day'),
                  selected: current.isSame(props.referenceDate, 'day'),
                  fill:     props.range[0].get('month') !== current.get('month')
                })}
                current={current}
              />
            )
          })}
        </div>
      )}
    </div>
  )
}

AgendaViewMonth.propTypes = {
  path: T.string.isRequired,
  referenceDate: T.object,
  range: T.arrayOf(T.object)
}

export {
  AgendaViewMonth
}
