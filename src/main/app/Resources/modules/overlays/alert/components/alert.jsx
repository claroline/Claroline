import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Alert as AlertTypes} from '#/main/app/overlays/alert/prop-types'
import {constants as actionConstants} from '#/main/app/action/constants'
import {Expire} from '#/main/app/components/expire'

import {constants} from '#/main/app/overlays/alert/constants'

/*const FlyingAlertIcon = props => props.showSecondary ?
  <span className={classes('flying-alert-icon fa fa-fw')}>
    <span className={classes('flying-alert-icon-primary fa fa-fw', props.primaryIcon)} />
    <span className={classes('flying-alert-icon-secondary fa', props.secondaryIcon)} />
  </span> :
  <span className={classes('flying-alert-icon fa fa-fw', props.primaryIcon)} />


FlyingAlertIcon.propTypes = {
  primaryIcon: T.string.isRequired,
  secondaryIcon: T.string,
  showSecondary: T.bool
}*/

const FlyingAlertContent = props => {
  const status = constants.ALERT_STATUS[props.status]
  const action = actionConstants.ACTIONS[props.action]

  return (
    <li
      className={classes('flying-alert', `flying-alert-${props.status}`, `flying-alert-${props.action}`, {
        removable: status.removable
      })}
      onClick={() => status.removable && props.removeAlert(props.id)}
    >
      <span className="flying-alert-icon">
        <span className={classes('fa fa-fw', /*action.icon ? action.icon : */status.icon)} />
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
