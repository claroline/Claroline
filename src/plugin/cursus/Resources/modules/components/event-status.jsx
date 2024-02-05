import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {now, trans} from '#/main/app/intl'

const EventStatus = (props) => {
  let status
  if (props.startDate > now(false)) {
    status = 'not_started'
  } else if (props.startDate <= now(false) && props.endDate >= now(false)) {
    status = 'in_progress'
  } else if (props.endDate < now(false)) {
    status = 'ended'
  }

  return (
    <span className={classes('badge', props.className, {
      'text-bg-success': 'not_started' === status,
      'text-bg-info': 'in_progress' === status,
      'text-bg-danger': 'ended' === status
    })}>
      {trans('session_'+status, {}, 'cursus')}
    </span>
  )
}

EventStatus.propTypes = {
  className: T.string,
  startDate: T.string,
  endDate: T.string
}

export {
  EventStatus
}
