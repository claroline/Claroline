import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Alert as AlertTypes} from '#/main/app/overlays/alert/prop-types'
import {Expire} from '#/main/app/components/expire'

import {constants} from '#/main/app/overlays/alert/constants'

const FlyingAlertContent = props => {
  const status = constants.ALERT_STATUS[props.status]

  let role = 'status'
  if ([
    constants.ALERT_STATUS_WARNING,
    constants.ALERT_STATUS_ERROR,
    constants.ALERT_STATUS_UNAUTHORIZED,
    constants.ALERT_STATUS_FORBIDDEN
  ].includes(status)) {
    role = 'alert'
  }

  return (
    <li
      className={classes('flying-alert mb-2', `flying-alert-${props.status}`, `flying-alert-${props.action}`, {
        removable: status.removable
      })}
      onClick={() => status.removable && props.removeAlert(props.id)}
      role={role}
    >
      <span className="flying-alert-icon">
        <span className={classes('fa fa-fw', status.icon)} />
      </span>

      <span className="flying-alert-message">
        <b className="flying-alert-title">
          {props.title}
        </b>

        {props.message}
      </span>
    </li>
  )
}

implementPropTypes(FlyingAlertContent, AlertTypes, {
  removeAlert: T.func.isRequired
})

const Alert = props => {
  const status = constants.ALERT_STATUS[props.status]

  if (status.timeout) {
    return (
      <Expire
        delay={status.timeout}
        onExpire={() => props.removeAlert(props.id)}
      >
        <FlyingAlertContent {...props} />
      </Expire>
    )
  }

  return (
    <FlyingAlertContent {...props} />
  )
}

implementPropTypes(Alert, AlertTypes, {
  removeAlert: T.func.isRequired
})

export {
  Alert
}
