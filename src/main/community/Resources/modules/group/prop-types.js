import {PropTypes as T} from 'prop-types'

const Group = {
  propTypes: {
    id: T.string,
    name: T.string,
    thumbnail: T.string,
    poster: T.string,
    meta: T.shape({
      description: T.string,
      readOnly: T.bool
    }),
    permissions: T.shape({
      open: T.bool,
      edit: T.bool,
      administrate: T.bool,
      delete: T.bool
    }),
    roles: T.arrayOf(T.object)
  },
  defaultProps: {}
}

export {
  Group
}
