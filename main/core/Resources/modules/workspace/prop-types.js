import {PropTypes as T} from 'prop-types'

const Workspace = {
  propTypes: {
    id: T.number,
    uuid: T.string,
    name: T.string,
    poster: T.string,
    roles: T.array,
    meta: T.shape({
      slug: T.string
    }).isRequired,
    registration: T.shape({
      validation: T.bool,
      selfRegistration: T.bool,
      selfUnregistration: T.bool
    }).isRequired,
    restrictions: T.shape({
      hidden: T.bool,
      maxUsers: T.number
    }).isRequired
  },
  defaultProps: {
    meta: {
      model: false
    },
    roles: [],
    registration: {
      validation: false,
      selfRegistration: false,
      selfUnregistration: false
    },
    restrictions: {
      hidden: false,
      maxUsers: null
    }
  }
}

export {
  Workspace
}
