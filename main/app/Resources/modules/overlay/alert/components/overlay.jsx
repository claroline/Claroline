import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Alert as AlertTypes} from '#/main/app/overlay/alert/prop-types'

import {Alert} from '#/main/app/overlay/alert/components/alert'

const AlertOverlay = props =>
  <ul className="flying-alerts">
    {props.alerts.map((alert, alertIndex) =>
      <Alert
        {...alert}
        key={alertIndex}
        removeAlert={props.removeAlert}
      />
    )}
  </ul>

AlertOverlay.propTypes = {
  alerts: T.arrayOf(T.shape(
    AlertTypes.propTypes
  )).isRequired,
  removeAlert: T.func.isRequired
}

export {
  AlertOverlay
}