import {PropTypes as T} from 'prop-types'

import {User} from '#/main/community/prop-types'

const Message = {
  propTypes: {
    id: T.string,
    content: T.string,
    object: T.string,
    to: T.string,
    from: T.shape(
      User.propTypes
    ),
    receivers: T.shape({
      users: T.arrayOf(T.shape({

      })),
      groups: T.arrayOf(T.shape({

      })),
      workspaces: T.arrayOf(T.shape({

      }))
    }),
    meta: T.shape({
      date: T.string.isRequired,
      read: T.bool.isRequired,
      removed: T.bool.isRequired,
      sent: T.bool.isRequired
    })
  },
  defaultProps: {
    meta: {
      read: false,
      removed : false,
      sent: false
    }
  }
}

export {
  Message
}
