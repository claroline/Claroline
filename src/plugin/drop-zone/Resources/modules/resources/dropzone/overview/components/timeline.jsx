import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {displayDate} from '#/main/app/intl/date'
import {trans} from '#/main/app/intl/translation'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

// todo display period dates

// https://momentjs.com/docs/#/durations/locale/
const EvaluationStatus = props =>
  <li className={classes('evaluation-status', {
    active: props.active
  })}>
    <h3 className="evaluation-status-heading">
      <span className={props.icon} aria-hidden={true} />
      {props.title}
    </h3>

    <div className="evaluation-planning">
      {props.children}
    </div>
  </li>

EvaluationStatus.propTypes = {
  icon: T.string.isRequired,
  title: T.string.isRequired,
  active: T.bool.isRequired,
  children: T.node
}

const Timeline = props =>
  <ul className="evaluation-timeline">
    <EvaluationStatus
      icon="fa fa-ban"
      title={constants.PLANNING_STATES.all[constants.STATE_NOT_STARTED]}
      active={constants.STATE_NOT_STARTED === props.state}
    >
      {trans('dropzone_start', {}, 'dropzone')} : {props.planning.type === constants.PLANNING_TYPE_AUTO && props.planning.drop && props.planning.drop.length > 0 ?
        <div><b>{displayDate(props.planning.drop[0], false, true)}</b></div> :
        <b>-</b>
      }
    </EvaluationStatus>

    <EvaluationStatus
      icon="fa fa-upload"
      title={constants.PLANNING_STATES.all[constants.STATE_ALLOW_DROP]}
      active={[
        constants.STATE_ALLOW_DROP,
        constants.STATE_ALLOW_DROP_AND_PEER_REVIEW
      ].indexOf(props.state) > -1}
    >
      {props.planning.type === constants.PLANNING_TYPE_AUTO && props.planning.drop && props.planning.drop.length > 0 ?
        trans('date_range', {
          start: displayDate(props.planning.drop[0], false, true),
          end: displayDate(props.planning.drop[1], false, true)
        }) :
        trans('manager_defined_period', {}, 'dropzone')
      }
    </EvaluationStatus>

    {constants.REVIEW_TYPE_PEER === props.reviewType &&
      <EvaluationStatus
        icon="fa fa-check-square"
        title={constants.PLANNING_STATES.all[constants.STATE_PEER_REVIEW]}
        active={[
          constants.STATE_PEER_REVIEW,
          constants.STATE_ALLOW_DROP_AND_PEER_REVIEW
        ].indexOf(props.state) > -1}
      >
        {props.planning.type === constants.PLANNING_TYPE_AUTO && props.planning.review && props.planning.review.length > 0 ?
          trans('date_range', {
            start: displayDate(props.planning.review[0], false, true),
            end: displayDate(props.planning.review[1], false, true)
          }) :
          trans('manager_defined_period', {}, 'dropzone')
        }
      </EvaluationStatus>
    }

    <EvaluationStatus
      icon="fa fa-flag-checkered"
      title={constants.PLANNING_STATES.all[constants.STATE_FINISHED]}
      active={constants.STATE_FINISHED === props.state}
    >
      {trans('dropzone_end', {}, 'dropzone')} : {props.planning.type === constants.PLANNING_TYPE_AUTO && props.planning.review && props.planning.review.length > 1 ?
        <div><b>{displayDate(props.planning.review[1], false, true)}</b></div> :
        <b>-</b>
      }
    </EvaluationStatus>
  </ul>

Timeline.propTypes = {
  state: T.oneOf(
    Object.keys(constants.PLANNING_STATES.all)
  ).isRequired,
  planning: T.shape({
    type: T.oneOf(
      Object.keys(constants.PLANNING_TYPES)
    ).isRequired,
    state: T.oneOf(
      Object.keys(constants.PLANNING_STATES.all)
    ),
    // drop date range
    drop: T.arrayOf(T.string),
    // review date range
    review: T.arrayOf(T.string)
  }).isRequired,
  reviewType: T.oneOf(
    Object.keys(constants.REVIEW_TYPES)
  ).isRequired
}

export {
  Timeline
}
