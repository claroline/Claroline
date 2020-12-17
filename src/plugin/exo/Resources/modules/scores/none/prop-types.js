import {PropTypes as T} from 'prop-types'

const ScoreNone = {
  propTypes: {
    type: T.oneOf(['none']).isRequired
  },

  defaultProps: {
    type: 'none'
  }
}

export {
  ScoreNone
}
