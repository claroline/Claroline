import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import moment from 'moment'
import tinycolor from 'tinycolor2'
import get from 'lodash/get'

import {LinkButton} from '#/main/app/buttons/link'
import {ModalButton} from '#/main/app/buttons'
import {now} from '#/main/app/intl/date'

import {CalendarView} from '#/plugin/agenda/tools/agenda/views/components/calendar'
import {constants} from '#/plugin/agenda/event/constants'
import {Event as EventTypes} from '#/plugin/agenda/event/prop-types'
import {sortEvents, eventDuration} from '#/plugin/agenda/event/utils'
import {route} from '#/plugin/agenda/tools/agenda/routing'
import {MODAL_EVENT_ABOUT} from '#/plugin/agenda/event/modals/about'

const ScheduleDay = (props) =>
  <div className={classes('day', props.className)}>
    <LinkButton
      className="day-number"
      target={route(props.path, 'month', props.current)}
    >
      {props.current.format('D')}
    </LinkButton>

    <div className="day-name">
      {props.current.format('MMM')}, {props.current.format('ddd')}
    </div>

    <div className="day-events">
      {sortEvents(props.events).map(event => {
        let color
        if (get(event, 'display.color')) {
          color = tinycolor(get(event, 'display.color'))
        }

        return (
          <ModalButton
            key={event.id}
            className={classes('agenda-event', {
              'done': event.meta.done
            })}
            modal={[MODAL_EVENT_ABOUT, {
              event: event,
              actions: props.eventActions(event)
            }]}
          >
            <span
              className="event-color icon-with-text-right"
              style={color ? {
                backgroundColor: color.toRgbString()
              } : undefined}
            />

            <span className="event-duration icon-with-text-right">
              {eventDuration(event)}
            </span>

            {constants.EVENT_TYPE_TASK === event.meta.type &&
              <span className="fa fa-fw fa-tasks icon-with-text-right" />
            }

            {event.title}
          </ModalButton>
        )
      })}
    </div>
  </div>

ScheduleDay.propTypes = {
  path: T.string.isRequired,
  className: T.string,
  current: T.object.isRequired,
  events: T.arrayOf(T.shape(
    EventTypes.propTypes
  )),
  eventActions: T.func.isRequired
}

const AgendaViewSchedule = (props) => {
  const nowDate = moment(now())

  // group events by day
  const schedule = {}
  props.events.map(event => {
    const start = moment(event.start)
    const end = moment(event.end)

    let startDay = start.format('YYYY-MM-DD')
    if (!schedule[startDay]) {
      schedule[startDay] = []
    }
    schedule[startDay].push(event)

    if (!end.isSame(start, 'day')) {
      while (start.add(1, 'days').isBefore(end)) {
        startDay = start.format('YYYY-MM-DD')
        if (!schedule[startDay]) {
          schedule[startDay] = []
        }
        schedule[startDay].push(event)
      }
    }
  })

  return (
    <CalendarView
      loaded={props.loaded}
      referenceDate={props.referenceDate}
      view={props.view}
      loadEvents={props.loadEvents}
    >
      <div className="agenda-schedule">
        {Object.keys(schedule)
          .sort((a, b) => a < b ? -1 : 1)
          .map(date => {
            const current = moment(date)

            return (
              <ScheduleDay
                key={date}
                path={props.path}
                className={classes({
                  now:      current.isSame(nowDate, 'day'),
                  selected: current.isSame(props.referenceDate, 'day')
                })}
                current={current}
                events={schedule[date]}
                eventActions={props.eventActions}
              />
            )
          })
        }
      </div>
    </CalendarView>
  )
}

AgendaViewSchedule.propTypes = {
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
  AgendaViewSchedule
}
