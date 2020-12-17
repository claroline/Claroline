import {PropTypes as T} from 'prop-types'

const Url = {
  propTypes: {
    url: T.string.isRequired,
    mode: T.string.isRequired,
    ratio: T.number.isRequired
  },
  defaultProps: {
    mode: 'redirect',
    ratio: 56.25
  }
}

export {
  Url
}
