import {PropTypes as T} from 'prop-types'

const Organization = {
  propTypes: {
    id: T.string,
    name: T.string,
    code: T.string,
    email: T.string,
    parent: T.shape({
      id: T.string.isRequired,
      name: T.string.isRequired
    })
  },
  defaultProps: {
    parent: null
  }
}

export {
  Organization
}
