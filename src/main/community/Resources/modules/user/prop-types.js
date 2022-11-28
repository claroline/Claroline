import {PropTypes as T} from 'prop-types'

const User = {
  propTypes: {
    id: T.string.isRequired,
    firstName: T.string,
    lastName: T.string,
    username: T.string.isRequired,
    picture: T.string,
    thumbnail: T.string,
    meta: T.shape({
      created: T.string,
      lastActivity: T.string,
      description: T.string,
      personalWorkspace: T.bool
    }),
    restrictions: T.shape({
      disabled: T.bool,
      dates: T.arrayOf(T.string)
    }),
    permissions: T.shape({
      contact: T.bool.isRequired,
      edit: T.bool.isRequired,
      administrate: T.bool.isRequired,
      delete: T.bool.isRequired
    }),
    roles: T.arrayOf(T.shape({
      id: T.string.isRequired,
      name: T.string.isRequired,
      translationKey: T.string.isRequired,
      type: T.number.isRequired
    }))
  },
  defaultProps: {
    permissions: {
      contact: false,
      edit: false,
      administrate: false,
      delete: false
    },
    restrictions: {
      disabled: false,
      dates: []
    }
  }
}

export {
  User
}
