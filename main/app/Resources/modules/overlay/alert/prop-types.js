import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/app/overlay/alert/constants'

const Alert = {
  propTypes: {
    id: T.string.isRequired,
    action: T.oneOf(
      Object.keys(constants.ALERT_ACTIONS)
    ).isRequired,
    status: T.oneOf(
      Object.keys(constants.ALERT_STATUS)
    ).isRequired,
    title: T.string,
    message: T.string.isRequired
  }
}

export {
  Alert
}
