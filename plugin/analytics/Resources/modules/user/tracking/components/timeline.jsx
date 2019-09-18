import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {LinkButton} from '#/main/app/buttons/link/components/button'

import {route as resourceRoute} from '#/main/core/resource/routing'
import {constants} from '#/main/core/user/tracking/constants'
import {ResourceIcon} from '#/main/core/resource/components/icon'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'

const EventWrapper = props =>
  <li className={classes('timeline-event-container', {
    'timeline-event-success': [constants.STATUS_PASSED, constants.STATUS_COMPLETED].indexOf(props.status) > -1,
    'timeline-event-partial': [constants.STATUS_PASSED, constants.STATUS_FAILED, constants.STATUS_COMPLETED].indexOf(props.status) === -1,
    'timeline-event-failure': constants.STATUS_FAILED === props.status
  })}>
    <span className={classes('timeline-event-icon', constants.TRACKING_EVENTS[props.type].icon)} />

    <div className="timeline-event">
      <span className="timeline-event-date">
        {displayDate(props.date, true, true)}
      </span>

      {props.status && <span className={classes('timeline-event-status', {
        'fa fa-fw fa-check': [constants.STATUS_PASSED, constants.STATUS_COMPLETED].indexOf(props.status) > -1,
        'fa fa-fw fa-minus': [constants.STATUS_PASSED, constants.STATUS_FAILED, constants.STATUS_COMPLETED].indexOf(props.status) === -1,
        'fa fa-fw fa-times': constants.STATUS_FAILED === props.status
      })} />}

      <div className="timeline-event-block">
        <div className="timeline-event-header">
          <LinkButton
            className="btn-link"
            primary={true}
            target={resourceRoute(props.resource)}
          >
            {props.resource.thumbnail ?
              <img
                src={asset(props.resource.thumbnail)}
                alt="resource_icon"
              /> :
              <ResourceIcon
                className="icon"
                mimeType={props.resource.meta.mimeType}
              />
            }
          </LinkButton>
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

        {props.progression && (!!props.progression[0] || !!props.progression[1]) &&
          <div className="timeline-event-progression">
            <ScoreGauge
              type="user"
              value={Math.round(props.progression[0])}
              total={props.progression[1]}
              width={70}
              height={70}
              displayValue={value => undefined === value || null === value ? '?' : value+''}
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
  status: T.oneOf(constants.TRACKING_STATUS),
  progression: T.array,
  type: T.oneOf(
    Object.keys(constants.TRACKING_EVENTS)
  ).isRequired,
  children: T.node.isRequired,
  resource: T.shape({
    autoId: T.number.isRequired,
    id: T.string.isRequired,
    name: T.string.isRequired,
    thumbnail: T.string,
    meta: T.shape({
      type: T.string.isRequired,
      mimeType: T.string.isRequired,
      icon: T.string.isRequired
    }).isRequired
  })
}

const EvaluationEvent = props =>
  <EventWrapper
    title={props.data.resourceNode.name}
    subtitle={trans(props.data.resourceNode.meta.type, {}, 'resource')}
    level={props.level}
    date={props.date}
    status={props.status}
    type={props.type}
    progression={props.progression}
    resource={props.data.resourceNode}
  >
    {trans('nb_attempts_participations', {}, 'platform')} : {props.data.nbAttempts}
    <br/>
    {trans('nb_openings', {}, 'platform')} : {props.data.nbOpenings}
    <br/>
    {trans('total_time', {}, 'platform')} : {props.data.duration ? `${props.data.duration} ${trans('seconds', {}, 'platform')}` : '-'}
  </EventWrapper>

EvaluationEvent.propTypes = {
  level: T.number.isRequired,
  date: T.string.isRequired,
  status: T.oneOf(constants.TRACKING_STATUS),
  type: T.oneOf(
    Object.keys(constants.TRACKING_EVENTS)
  ).isRequired,
  progression: T.array,
  data: T.shape({
    resourceNode: T.shape({
      name: T.string.isRequired,
      meta: T.shape({
        type: T.string.isRequired
      }).isRequired
    }),
    nbAttempts: T.number,
    nbOpenings: T.number,
    duration: T.number
  })
}

const Timeline = props =>
  <ul className="user-timeline">
    <li className="timeline-endpoint timeline-event-date">
      {trans('today', {}, 'platform')}
    </li>
    {props.events.map((event, eventIndex) =>
      <EvaluationEvent
        key={eventIndex}
        level={props.level}
        {...event}
      />
    )}
    {props.events.length > 0 &&
      <li className="timeline-endpoint timeline-event-date">
        {displayDate(props.events[props.events.length - 1].date, false)}
      </li>
    }
  </ul>

Timeline.propTypes = {
  level: T.number,
  events: T.arrayOf(T.shape({
    date: T.string.isRequired,
    type: T.string.isRequired,
    status: T.string,
    progression: T.array,
    data: T.object
  })).isRequired
}

Timeline.defaultProps = {
  level: 3
}

export {
  Timeline
}
