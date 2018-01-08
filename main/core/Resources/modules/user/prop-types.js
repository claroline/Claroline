import {PropTypes as T} from 'prop-types'

const User = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string.isRequired,
    username: T.string.isRequired,
    picture: T.string
  }
}

export {
  User
}
