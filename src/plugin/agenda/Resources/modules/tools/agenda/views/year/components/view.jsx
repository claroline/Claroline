import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import moment from 'moment'
import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'
import {now} from '#/main/app/intl/date'
import {LinkButton} from '#/main/app/buttons/link/components/button'
import {PopoverButton} from '#/main/app/buttons/popover/components/button'
import {UrlButton} from '#/main/app/buttons/url/components/button'

import {EventMicro} from '#/plugin/agenda/event/components/micro'
import {sortEvents} from '#/plugin/agenda/event/utils'
import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {route} from '#/plugin/agenda/tools/agenda/routing'

const DayPopover = props =>
  <PopoverButton
    id={props.id}
    type="callback"
    className={classes('btn-link day-number', props.className)}
    onClick={() => {
      props.loadEvents([
        moment(props.current).hour(0).minute(0).second(0),
        moment(props.current).hour(23).minute(59).second(59)
      ])
    }}
    popover={{
      className: classes('day-popover', props.className),
      position: 'top',
      label: (
        <Fragment>
          {props.current.format('dddd')}

          <UrlButton
            className="day-number btn-link"
            target={'#'+route(props.path, 'day', props.current)}
          >
            {props.current.format('D')}
          </UrlButton>
        </Fragment>
      ),
      content: (
        <Fragment>
          {0 === props.events.length && trans('no_event', {}, 'agenda')}

          {0 !== props.events.length && sortEvents(props.events).map(event => (
            <EventMicro
              key={event.id}
              event={event}
              actions={props.eventActions(event)}
            />
          ))}
        </Fragment>
      )
    }}
  >
    {props.current.format('D')}
  </PopoverButton>

DayPopover.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  path: T.string.isRequired,
  current: T.object.isRequired,

  loadEvents: T.func.isRequired,
  events: T.arrayOf(T.shape(
    EventTypes.propTypes
  )).isRequired,
  eventActions: T.func.isRequired
}

const Month = props =>
  <table cellSpacing="0" cellPadding="0">
    <thead>
      <tr>
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
          {times(7, (dayNum) => {
            const current = moment(props.currentRange[0])
              .week(props.currentRange[0].week()+weekNum)
              .weekday(dayNum)

            return (
              <td key={`day-${weekNum}-${dayNum}`}>
                <DayPopover
                  id={`day-popover-${weekNum}-${dayNum}`}
                  path={props.path}
                  className={classes({
                    now:      current.isSame(props.now, 'day'),
                    selected: current.isSame(props.referenceDate, 'day'),
                    fill:     props.currentRange[0].get('month') !== current.get('month')
                  })}
                  current={current}
                  loaded={props.loaded}
                  loadEvents={props.loadEvents}
                  events={props.events.filter((event) => {
                    const start = moment(event.start)
                    const end = moment(event.end)

                    return start.isSameOrBefore(current, 'day') && end.isSameOrAfter(current, 'day')
                  })}
                  eventActions={props.eventActions}
                />
              </td>
            )
          })}
        </tr>
      )}
    </tbody>
  </table>

Month.propTypes = {
  path: T.string.isRequired,
  now: T.object,
  referenceDate: T.object,
  currentRange: T.arrayOf(T.object),

  loaded: T.bool.isRequired,
  loadEvents: T.func.isRequired,
  events: T.arrayOf(T.shape(
    EventTypes.propTypes
  )).isRequired,
  eventActions: T.func.isRequired
}

const AgendaViewYear = (props) =>
  <div className="agenda-year row">
    {times(12, (monthNum) =>
      <div key={`month-${monthNum}`} className="col-md-3 col-sm-4 col-xs-6">
        <LinkButton
          className="h4 month-name"
          target={route(props.path, 'month', moment(props.referenceDate).month(monthNum))}
        >
          {moment().month(monthNum).format('MMMM')}
        </LinkButton>

        <Month
          path={props.path}
          referenceDate={props.referenceDate}
          now={moment(now())}
          currentRange={[moment(props.range[0]).month(monthNum)]}
          loaded={props.loaded}
          loadEvents={props.loadEvents}
          events={props.events}
          eventActions={props.eventActions}
        />
      </div>
    )}
  </div>

AgendaViewYear.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }),
  path: T.string.isRequired,
  loaded: T.bool.isRequired,
  view: T.string.isRequired,
  referenceDate: T.object,
  range: T.arrayOf(T.object),
  previous: T.func.isRequired,
  next: T.func.isRequired,

  loadEvents: T.func.isRequired,
  events: T.arrayOf(T.shape(
    EventTypes.propTypes
  )).isRequired,
  create: T.func.isRequired,
  eventActions: T.func.isRequired
}

export {
  AgendaViewYear
}
