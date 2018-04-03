import {PropTypes as T} from 'prop-types'

const Role = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string.isRequired,
    translationKey: T.string.isRequired
  }
}

export {
  Role
}
