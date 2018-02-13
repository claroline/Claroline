import {PropTypes as T} from 'prop-types'

const User = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string.isRequired,
    username: T.string.isRequired,
    picture: T.shape({
      url: T.string.isRequired
    }),
    meta: T.shape({
      publicUrl: T.string
    })
  }
}

export {
  User
}
