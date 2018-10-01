import {PropTypes as T} from 'prop-types'

import {User as UserType} from '#/main/core/user/prop-types'

const Message = {
  propTypes: {
    id: T.string,
    content: T.string,
    object: T.string,
    to: T.string,
    from: T.shape(UserType.propTypes),
    meta: T.shape({
      date: T.string.isRequired,
      read: T.bool.isRequired,
      removed: T.bool.isRequired,
      sent: T.bool.isRequired
    })
  },
  defaultProps: {
    meta: {
      removed : false
    }
  }
}



export {
  Message
}
