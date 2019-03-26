import {PropTypes as T} from 'prop-types'


const MatchItem = {
  propTypes: {
    id: T.string.isRequired,
    random: T.bool.isRequired,
    penalty: T.number.isRequired,
    firstSet: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired,
    secondSet: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired,
    solutions: T.arrayOf(T.shape({
      firstId: T.string.isRequired,
      secondId: T.string.isRequired,
      score: T.number.isRequired,
      feedback: T.string
    })).isRequired,
    _errors: T.object
  },
  defaultProps: {
    firstSet: [],
    secondSet: [],
    solutions: [],
    random: false,
    penalty: 0
  }
}

export {
  MatchItem
}
