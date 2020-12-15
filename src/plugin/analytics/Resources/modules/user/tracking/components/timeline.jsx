import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {schemeCategory20c} from 'd3-scale'

import {asset} from '#/main/app/config/asset'
import {toKey} from '#/main/core/scaffolding/text'
import {trans, number, displayDate, displayDuration} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'

import {UserEvaluation as ResourceUserEvaluationTypes} from '#/main/core/resource/prop-types'
import {DataCard} from '#/main/app/data/components/card'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {constants} from '#/plugin/analytics/user/tracking/constants'

const EvaluationEvent = props => {
  let progression = 0
  if (props.data.progression) {
    progression = props.data.progression
    if (props.data.progressionMax) {
      progression = (progression / props.data.progressionMax) * 100
    }
  }

  return (
    <li className={classes('timeline-event-container', {
      'timeline-event-success': [constants.STATUS_PASSED, constants.STATUS_COMPLETED, constants.STATUS_PARTICIPATED].indexOf(props.data.status) > -1,
      'timeline-event-partial': [constants.STATUS_PASSED, constants.STATUS_FAILED, constants.STATUS_COMPLETED, constants.STATUS_PARTICIPATED].indexOf(props.data.status) === -1,
      'timeline-event-failure': constants.STATUS_FAILED === props.data.status
    })}>
      <span className={classes('timeline-event-icon', constants.TRACKING_EVENTS[props.type].icon)} />

      <div className="timeline-event">
        <span className="timeline-event-date">
          {displayDate(props.date, true, true)}
        </span>

        {props.data.status &&
          <span className={classes('timeline-event-status', {
            'fa fa-fw fa-check': [constants.STATUS_PASSED, constants.STATUS_COMPLETED, constants.STATUS_PARTICIPATED].indexOf(props.data.status) > -1,
            'fa fa-fw fa-minus': [constants.STATUS_PASSED, constants.STATUS_FAILED, constants.STATUS_COMPLETED, constants.STATUS_PARTICIPATED].indexOf(props.data.status) === -1,
            'fa fa-fw fa-times': constants.STATUS_FAILED === props.data.status
          })} />
        }

        <DataCard
          id={props.data.resourceNode.id}
          className="resource-evaluation-card"
          poster={props.data.resourceNode.thumbnail ? asset(props.data.resourceNode.thumbnail.url) : null}
          icon={
            <LiquidGauge
              id={`user-progression-${props.data.resourceNode.id}`}
              type="user"
              value={progression}
              displayValue={(value) => number(value) + '%'}
              width={60}
              height={60}
            />
          }
          title={props.data.resourceNode.name}
          subtitle={trans(props.data.resourceNode.meta.type, {}, 'resource')}
          actions={[
            {
              name: 'open',
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-external-link',
              label: trans('open', {}, 'actions'),
              target: resourceRoute(props.data.resourceNode)
            }
          ]}
        >
          <div className="resource-evaluation-details">
            {[
              {
                icon: 'fa fa-fw fa-eye',
                label: trans('views'),
                value: number(props.data.nbOpenings)
              }, {
                icon: 'fa fa-fw fa-redo',
                label: trans('attempts'),
                value: number(props.data.nbAttempts)
              }, {
                icon: 'fa fa-fw fa-hourglass-half',
                label: 'Temps passé',
                value: displayDuration(props.data.duration) || trans('unknown')
              }, {
                icon: 'fa fa-fw fa-award',
                label: trans('score'),
                displayed: !!props.data.scoreMax,
                value: (number(props.data.score) || 0) + ' / ' + number(props.data.scoreMax)
              }
            ]
              .filter(item => undefined === item.displayed || item.displayed)
              .map((item, index) => (
                <article key={toKey(item.label)}>
                  <span className={item.icon} style={{backgroundColor: schemeCategory20c[(index * 4) + 1]}} />
                  <h5>
                    <small>{item.label}</small>
                    {item.value}
                  </h5>
                </article>
              ))
            }
          </div>
        </DataCard>
      </div>
    </li>
  )
}

EvaluationEvent.propTypes = {
  level: T.number.isRequired,
  date: T.string.isRequired,
  type: T.oneOf(
    Object.keys(constants.TRACKING_EVENTS)
  ).isRequired,
  children: T.node.isRequired,
  data: T.shape(
    ResourceUserEvaluationTypes.propTypes
  )
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
    data: T.shape(
      ResourceUserEvaluationTypes.propTypes
    )
  })).isRequired
}

Timeline.defaultProps = {
  level: 3
}

export {
  Timeline
}
