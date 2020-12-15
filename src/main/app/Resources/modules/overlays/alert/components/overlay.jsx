import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Alert as AlertTypes} from '#/main/app/overlays/alert/prop-types'

import {Alert} from '#/main/app/overlays/alert/components/alert'

// TODO : create portal and style it. so when there are embedded apps, alerts will not overlaps

const AlertOverlay = props => {
  if (props.alerts && 0 < props.alerts.length) {
    return (
      <ul className="flying-alerts">
        {props.alerts.map((alert, alertIndex) =>
          <Alert
            {...alert}
            key={alertIndex}
            removeAlert={props.removeAlert}
          />
        )}
      </ul>
    )
  }

  return null
}

AlertOverlay.propTypes = {
  alerts: T.arrayOf(T.shape(
    AlertTypes.propTypes
  )).isRequired,
  removeAlert: T.func.isRequired
}

export {
  AlertOverlay
}