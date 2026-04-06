import React, {useState, useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'

/**
 * Displays a countdown timer to a deadline.
 * Shows days/hours/minutes remaining, with color-coded urgency.
 *
 * @param {string} deadline - ISO date string of the deadline
 */
const DeadlineCountdown = ({deadline}) => {
  const [remaining, setRemaining] = useState(null)

  useEffect(() => {
    if (!deadline) {
      return
    }

    const computeRemaining = () => {
      const now = new Date()
      const end = new Date(deadline)
      const diff = end.getTime() - now.getTime()

      if (diff <= 0) {
        return {expired: true, days: 0, hours: 0, minutes: 0, seconds: 0, totalMs: 0}
      }

      const days = Math.floor(diff / (1000 * 60 * 60 * 24))
      const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
      const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
      const seconds = Math.floor((diff % (1000 * 60)) / 1000)

      return {expired: false, days, hours, minutes, seconds, totalMs: diff}
    }

    setRemaining(computeRemaining())

    const interval = setInterval(() => {
      setRemaining(computeRemaining())
    }, 60000) // Update every minute

    return () => clearInterval(interval)
  }, [deadline])

  if (!deadline) {
    return null
  }

  if (!remaining) {
    return null
  }

  // Determine urgency level
  const isUrgent = !remaining.expired && remaining.totalMs < 24 * 60 * 60 * 1000 // Less than 24h
  const isWarning = !remaining.expired && remaining.totalMs < 3 * 24 * 60 * 60 * 1000 // Less than 3 days

  return (
    <div className={classes('deadline-countdown d-flex align-items-center gap-2 p-2 rounded', {
      'bg-danger-subtle text-danger': remaining.expired,
      'bg-warning-subtle text-warning': isUrgent && !remaining.expired,
      'bg-info-subtle text-info': isWarning && !isUrgent && !remaining.expired,
      'bg-body-tertiary text-body-secondary': !isWarning && !remaining.expired
    })}>
      <span className={classes('fa fa-fw', {
        'fa-clock': !remaining.expired,
        'fa-exclamation-triangle': remaining.expired
      })} />

      {remaining.expired ? (
        <span className="fw-bold">
          {trans('deadline_expired', {}, 'project')}
        </span>
      ) : (
        <span>
          <span className="fw-bold">
            {trans('deadline', {}, 'project')} :
          </span>
          {' '}
          {remaining.days > 0 && (
            <span>{remaining.days} {trans('days')} </span>
          )}
          <span>{remaining.hours}h {String(remaining.minutes).padStart(2, '0')}min</span>
          {' '}
          <small className="text-muted">
            ({displayDate(deadline, false, true)})
          </small>
        </span>
      )}
    </div>
  )
}

DeadlineCountdown.propTypes = {
  deadline: T.string
}

export {
  DeadlineCountdown
}
