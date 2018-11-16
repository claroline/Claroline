import {PropTypes as T} from 'prop-types'

const Tag = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string.isRequired,
    meta: T.shape({
      description: T.string,
      creator: T.shape({
        // TODO : minimal user
      })
    }),
    elements: T.number
  },
  defaultProps: {
    meta: {},
    elements: 0
  }
}

export {
  Tag
}
