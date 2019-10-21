import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import moment from 'moment'
import times from 'lodash/times'

import {LinkButton} from '#/main/app/buttons/link'
import {now} from '#/main/app/intl/date'
import {route} from '#/plugin/agenda/tools/agenda/routing'

const Day = props =>
  <div className={classes('calendar-col day', props.className)}>
    <div className="day-name">
      {props.current.format('ddd')}

      <LinkButton
        className="day-number"
        target={route(props.path, 'day', props.current)}
      >
        {props.current.format('D')}
      </LinkButton>
    </div>
  </div>

Day.propTypes = {
  path: T.string.isRequired,
  className: T.string,
  current: T.object.isRequired
}

const AgendaViewWeek = (props) => {
  const nowDate = moment(now())

  return (
    <div className="agenda-week">
      <div className="calendar-col hours">
      </div>

      {times(7, (dayNum) => {
        const current = moment(props.range[0])
          .weekday(dayNum)

        return (
          <Day
            key={`day-${dayNum}`}
            path={props.path}
            className={classes({
              now:      current.isSame(nowDate, 'day'),
              selected: current.isSame(props.referenceDate, 'day')
            })}
            current={current}
          />
        )
      })}
    </div>
  )
}

AgendaViewWeek.propTypes = {
  path: T.string.isRequired,
  referenceDate: T.object,
  range: T.arrayOf(T.object)
}

export {
  AgendaViewWeek
}
