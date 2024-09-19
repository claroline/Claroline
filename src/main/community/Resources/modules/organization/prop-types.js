import {PropTypes as T} from 'prop-types'

const Organization = {
  propTypes: {
    id: T.string,
    name: T.string,
    code: T.string,
    thumbnail: T.string,
    poster: T.string,
    email: T.string,
    meta: T.shape({
      description: T.string
    }),
    restrictions: T.shape({
      public: T.bool
    })
  },
  defaultProps: {}
}

export {
  Organization
}
