import {PropTypes as T} from 'prop-types'

const Track = {
  propTypes: {
    id: T.string.isRequired,
    autoId: T.number,
    meta: T.shape({
      label: T.string,
      lang: T.string,
      kind: T.string,
      default: T.bool
    })
  }
}

export {
  Track
}