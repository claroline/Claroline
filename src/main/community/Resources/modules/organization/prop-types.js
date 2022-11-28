import {PropTypes as T} from 'prop-types'

const Organization = {
  propTypes: {
    id: T.string,
    name: T.string,
    code: T.string,
    thumbnail: T.string,
    poster: T.string,
    email: T.string,
    parent: T.shape({
      id: T.string.isRequired,
      name: T.string.isRequired
    }),
    meta: T.shape({
      description: T.string
    }),
    restrictions: T.shape({
      public: T.bool,
      users: T.number
    })
  },
  defaultProps: {
    parent: null
  }
}

export {
  Organization
}
