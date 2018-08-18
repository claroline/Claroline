import {PropTypes as T} from 'prop-types'

const File = {
  propTypes: {
    hashName: T.string.isRequired,
    size: T.number.isRequired,
    url: T.string.isRequired
  },
  defaultProps: {
    size: 0
  }
}

export {
  File
}
