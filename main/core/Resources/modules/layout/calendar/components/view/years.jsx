import React from 'react'
import classes from 'classnames'
import times from 'lodash/times'
import moment from 'moment'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {Button} from '#/main/core/layout/button/components/button.jsx'

import {CalendarView as CalendarViewTypes} from '#/main/core/layout/calendar/prop-types'
import {CalendarLayout} from '#/main/core/layout/calendar/components/view/layout.jsx'
import {constants} from '#/main/core/layout/calendar/constants'
import {yearNum} from '#/main/core/layout/calendar/utils'

const Year = props =>
  <Button
    className={classes('btn-link-default', {
      now:      props.current.isSame(props.now, 'year'),
      selected: props.selected && props.current.isSame(props.selected, 'year'),
      fill:     props.fill
    })}
    disabled={props.current.isBefore(props.calendarRange[0], 'year') || props.current.isAfter(props.calendarRange[1], 'year')}
    onClick={props.onClick}
  >
    {props.current.format('YYYY')}
  </Button>

Year.propTypes = {
  calendarRange: T.arrayOf(
    T.object
  ).isRequired,
  current: T.object.isRequired,
  now: T.object.isRequired,
  selected: T.object,
  fill: T.bool.isRequired,
  onClick: T.func.isRequired
}

/**
 * Displays a grid with 12 years (one row for 4 years).
 *
 * @param props
 * @return {XML}
 * @constructor
 */
const Years = props => {
  // get the closer decade start (aka. 1980, 2010, 2020)
  const decadeStart = (Math.trunc(props.currentRange[0].get('year') / 10) * 10)
  const decadeEnd = decadeStart + 9

  // we will start one year earlier and end one year later than the decade
  // because our grid is 12 cells (not 10)
  const firstYear = decadeStart - 1

  return (
    <CalendarLayout
      view={constants.CALENDAR_VIEW_YEARS}
      title={`${moment({year: decadeStart}).format('YYYY')} - ${moment({year: decadeEnd}).format('YYYY')}`}
      changeView={props.changeView}
      calendarRange={props.calendarRange}
      previousRange={[
        moment(props.currentRange[0]).set('year', decadeStart).subtract(10, 'year'),
        moment(props.currentRange[1]).set('year', decadeEnd).subtract(10, 'year')
      ]}
      nextRange={[
        moment(props.currentRange[0]).set('year', decadeStart).add(10, 'year'),
        moment(props.currentRange[1]).set('year', decadeEnd).add(10, 'year')
      ]}
    >
      {times(3, (rowNum) =>
        <div key={`row-${rowNum}`} className="calendar-row">
          {times(4, (rowYearNum) =>
            <Year
              key={`year-${rowNum}-${rowYearNum}`}
              calendarRange={props.calendarRange}
              current={moment(props.currentRange[0]).set('year', firstYear+yearNum(rowNum, rowYearNum))}
              now={props.now}
              selected={props.selected}
              fill={firstYear+yearNum(rowNum, rowYearNum) < decadeStart || firstYear+yearNum(rowNum, rowYearNum) > decadeEnd}
              onClick={() => props.changeView(constants.CALENDAR_VIEW_MONTHS, [
                moment(props.currentRange[0]).set('year', firstYear+yearNum(rowNum, rowYearNum)),
                moment(props.currentRange[1]).set('year', firstYear+yearNum(rowNum, rowYearNum))
              ])}
            />
          )}
        </div>
      )}
    </CalendarLayout>
  )
}

implementPropTypes(Years, CalendarViewTypes)

export {
  Years
}
