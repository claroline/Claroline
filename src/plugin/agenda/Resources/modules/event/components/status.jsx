import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {now, trans} from '#/main/app/intl'
import {Badge} from '#/main/app/components/badge'

function getPeriodStatus(startDate, endDate) {
  let status
  if (startDate > now(false)) {
    status = 'not_started'
  } else if (startDate <= now(false) && endDate >= now(false)) {
    status = 'in_progress'
  } else if (endDate < now(false)) {
    status = 'ended'
  }

  return status
}

const EventStatus = (props) => {
  const status = getPeriodStatus(props.startDate, props.endDate)

  return (
    <Badge
      className={props.className}
      variant={classes({
        'secondary': 'not_started' === status,
        'success': 'in_progress' === status,
        'danger': 'ended' === status
      })}
      subtle={props.subtle}
    >
      {trans(status)}
    </Badge>
  )
}

EventStatus.propTypes = {
  className: T.string,
  startDate: T.string,
  endDate: T.string,
  subtle: T.bool
}

export {
  EventStatus
}
