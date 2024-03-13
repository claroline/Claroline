import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {toKey} from '#/main/core/scaffolding/text'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

import {constants} from '#/main/app/user/constants'


const UserStatusBullet = (props) =>
  <span
    className={classes('user-status-bullet rounded-circle', `bg-${constants.USER_STATUS_COLORS[props.status]}`)}
    aria-hidden={true}
  />

UserStatusBullet.propTypes = {
  status: T.string.isRequired
}

const UserStatusLabel = (props) =>
  <span className={props.className} role="presentation">{constants.USER_STATUSES[props.status]}</span>

UserStatusLabel.propTypes = {
  className: T.string,
  status: T.string.isRequired
}

const UserStatus = (props) => {
  const status = get(props.user, 'status', constants.USER_STATUS_OFFLINE)

  if (!props.noText && props.tooltip) {
    return (
      <TooltipOverlay
        id={`tooltip-${toKey(props.user.name)}-status`}
        tip={constants.USER_STATUSES[status]}
        position={props.tooltip}
      >
        <span className={classes('user-status', props.className)} role="presentation">
          <span
            className={classes('user-status-bullet rounded-circle', `bg-${constants.USER_STATUS_COLORS[status]}`)}
            aria-hidden={true}
          />

          <span className="visually-hidden">{constants.USER_STATUSES[status]}</span>
        </span>
      </TooltipOverlay>
    )
  }

  const className = classes('user-status', props.className)

  switch (props.variant) {
    case 'text':
      return (
        <span className={className} role="presentation">
          <UserStatusBullet status={status} />
          <UserStatusLabel status={status} />
        </span>
      )

    case 'badge':
      return (
        <span className={classes(className, `badge text-${constants.USER_STATUS_COLORS[status]}-emphasis bg-${constants.USER_STATUS_COLORS[status]}-subtle`)} role="presentation">
          <UserStatusBullet status={status} />
          <UserStatusLabel status={status} />
        </span>
      )

    case 'tooltip':
      return (
        <TooltipOverlay
          id={`tooltip-${toKey(props.user.name)}-status`}
          tip={constants.USER_STATUSES[status]}
          position="bottom"
        >
          <span className={classes('user-status', props.className)} role="presentation">
            <UserStatusBullet status={status} />
            <UserStatusLabel status={status} className="visually-hidden" />
          </span>
        </TooltipOverlay>
      )

    case 'bullet':
      return (
        <span className={classes('user-status', props.className)} role="presentation">
          <UserStatusBullet status={status} />
          <UserStatusLabel status={status} className="visually-hidden" />
        </span>
      )
  }

  return (
    <span className={classes('user-status', props.className)} role="presentation">
      <span
        className={classes('user-status-bullet rounded-circle', `bg-${constants.USER_STATUS_COLORS[status]}`)}
        aria-hidden={true}
      />

      <span className={props.noText ? 'visually-hidden' : undefined}>{constants.USER_STATUSES[status]}</span>
    </span>
  )
}

UserStatus.propTypes = {
  className: T.string,
  user: T.shape({
    name: T.string.isRequired,
    lastActivity: T.string,
    status: T.string
  }),
  variant: T.oneOf([
    'text', // a colored bullet + textual status
    'tooltip', // a colored bullet + textual status shown in a tooltip on hover
    'badge', // a colored bullet + textual status inside "badge" styles
    'bullet' // a colored bullet without any text
  ])
}

UserStatus.defaultProps = {
  variant: 'text'
}

export {
  UserStatus
}
