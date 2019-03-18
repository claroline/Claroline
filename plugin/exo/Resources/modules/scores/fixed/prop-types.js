import {PropTypes as T} from 'prop-types'

const ScoreFixed = {
  propTypes: {
    success: T.number.isRequired,
    failure: T.number.isRequired
  },

  defaultProps: {
    failure: 0
  }
}

export {
  ScoreFixed
}
