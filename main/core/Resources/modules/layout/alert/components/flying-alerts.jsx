import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {Alert as AlertTypes} from '#/main/core/layout/alert/prop-types'
import {constants as actionConstants} from '#/main/core/layout/action/constants'
import {Expire} from '#/main/core/layout/components/expire.jsx'

import {constants} from '#/main/core/layout/alert/constants'

// todo handle auto hide

const FlyingAlertIcon = props => props.showSecondary ?
  <span className={classes('flying-alert-icon fa fa-fw')}>
    <span className={classes('flying-alert-icon-primary fa fa-fw', props.primaryIcon)} />
    <span className={classes('flying-alert-icon-secondary fa', props.secondaryIcon)} />
  </span> :
  <span className={classes('flying-alert-icon fa fa-fw', props.primaryIcon)} />


FlyingAlertIcon.propTypes = {
  primaryIcon: T.string.isRequired,
  secondaryIcon: T.string,
  showSecondary: T.bool
}

const FlyingAlert = props => {
  const status = constants.ALERT_STATUS[props.status]
  const action = actionConstants.ACTIONS[props.action]

  return (
    <li
      className={classes('flying-alert', `flying-alert-${props.status}`, `flying-alert-${props.action}`, {
        removable: status.removable
      })}
      onClick={() => status.removable && props.removeAlert(props.id)}
    >
      <FlyingAlertIcon
        primaryIcon={action.icon ? action.icon : status.icon}
        secondaryIcon={status.icon}
        showSecondary={action.icon && constants.ALERT_STATUS_PENDING !== props.status}
      />

      <span className="flying-alert-message">
        <span className="flying-alert-title">
          {props.title}
        </span>

        {props.message}
      </span>
    </li>
  )
}

implementPropTypes(FlyingAlert, AlertTypes, {
  removeAlert: T.func.isRequired
})

const FlyingAlertContainer = props => {
  const status = constants.ALERT_STATUS[props.status]

  if (status.timeout) {
    return (
      <Expire
        delay={status.timeout}
        onExpire={() => props.removeAlert(props.id)}
      >
        <FlyingAlert {...props} />
      </Expire>
    )
  }

  return (
    <FlyingAlert {...props} />
  )
}

implementPropTypes(FlyingAlertContainer, AlertTypes, {
  removeAlert: T.func.isRequired
})

const FlyingAlerts = props =>
  <ul className="flying-alerts">
    {props.alerts.map((alert, alertIndex) =>
      <FlyingAlertContainer
        {...alert}

        key={alertIndex}
        removeAlert={props.removeAlert}
      />
    )}
  </ul>

FlyingAlerts.propTypes = {
  alerts: T.arrayOf(T.shape(
    AlertTypes.propTypes
  )).isRequired,
  removeAlert: T.func.isRequired
}

export {
  FlyingAlerts
}
