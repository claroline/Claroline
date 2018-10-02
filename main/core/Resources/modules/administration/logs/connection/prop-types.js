import {PropTypes as T} from 'prop-types'

import {User as UserType} from '#/main/core/user/prop-types'

const LogConnectPlatform = {
  propTypes: {
    id: T.string.isRequired,
    date: T.string.isRequired,
    user: T.shape(UserType.propTypes).isRequired,
    duration: T.number
  }
}

export {
  LogConnectPlatform
}
