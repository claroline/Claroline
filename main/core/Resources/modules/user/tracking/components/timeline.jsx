import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {localeDate} from '#/main/core/scaffolding/date'
import {constants} from '#/main/core/user/tracking/constants'

import {ScoreGauge} from '#/main/core/layout/progression/components/score-gauge.jsx'

const EventWrapper = props =>
  <li className={classes('timeline-event-container', {
    'timeline-event-success': 'success' === props.status,
    'timeline-event-partial': 'partial' === props.status,
    'timeline-event-failure': 'failure' === props.status
  })}>
    <span className={classes('timeline-event-icon', constants.TRACKING_EVENTS[props.type].icon)} />

    <div className="timeline-event">
      <span className="timeline-event-date">
        {localeDate(props.date, true)}
      </span>

      {props.status && <span className={classes('timeline-event-status', {
        'fa fa-fw fa-check': 'success' === props.status,
        'fa fa-fw fa-minus': 'partial' === props.status,
        'fa fa-fw fa-times': 'failure' === props.status
      })} />}

      <div className="timeline-event-block">
        <div className="timeline-event-header">

        </div>

        <div className="timeline-event-content">
          {React.createElement('h'+props.level, {
            className: 'timeline-event-title'
          }, [
            props.title,
            props.subtitle && <small key="event-subtitle">{props.subtitle}</small>
          ])}

          {props.children}
        </div>

        {props.progression &&
        <div className="timeline-event-progression">
          <ScoreGauge
            userScore={props.progression[0]}
            maxScore={props.progression[1]}
          />
        </div>
        }
      </div>
    </div>
  </li>

EventWrapper.propTypes = {
  level: T.number.isRequired,
  date: T.string.isRequired,
  title: T.string.isRequired,
  subtitle: T.string,
  status: T.oneOf(['success', 'partial', 'failure']),
  progression: T.array,
  type: T.oneOf(
    Object.keys(constants.TRACKING_EVENTS)
  ).isRequired,
  children: T.node.isRequired
}

const EvaluationEvent = props =>
  <EventWrapper
    title="Name of my resource"
    subtitle="Questionnaire"
    level={props.level}
    date={props.date}
    status={props.status}
    type={props.type}
    progression={props.progression}
  >
    EVENT CONTENT
  </EventWrapper>

EvaluationEvent.propTypes = {
  level: T.number.isRequired,
  date: T.string.isRequired,
  status: T.oneOf(['success', 'partial', 'failure']),
  type: T.oneOf(
    Object.keys(constants.TRACKING_EVENTS)
  ).isRequired,
  progression: T.array
}

const Timeline = props =>
  <ul className="user-timeline">
    <li className="timeline-endpoint timeline-event-date">aujourd'hui</li>
    {props.events.map((event, eventIndex) =>
      <EvaluationEvent
        key={eventIndex}
        level={props.level}
        {...event}
      />
    )}
    <li className="timeline-endpoint timeline-event-date">03/12/2017</li>
  </ul>

Timeline.propTypes = {
  level: T.number,
  events: T.arrayOf(T.shape({

  })).isRequired
}

Timeline.defaultProps = {
  level: 3
}

export {
  Timeline
}
