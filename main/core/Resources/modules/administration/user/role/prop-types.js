import {PropTypes as T} from 'prop-types'

const Role = {
  propTypes: {
    id: T.string,
    name: T.string,
    meta: T.shape({
      users: T.number,
      readOnly: T.bool
    }),
    restrictions: T.shape({
      maxUsers: T.number
    }),
    adminTools: T.object
  },
  defaultProps: {

  }
}

export {
  Role
}
