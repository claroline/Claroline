import {PropTypes as T} from 'prop-types'

import {Quiz} from '#/plugin/exo/resources/quiz/prop-types'

const Paper = {
  propTypes: {
    id: T.string.isRequired,
    number: T.number.isRequired,
    startDate: T.string.isRequired,
    endDate: T.string,
    user: T.shape({
      // TODO : user type
    }),
    score: T.number,
    finished: T.bool.isRequired,

    // not available in minimal mode (aka in list)
    structure: T.shape(
      Quiz.propTypes
    ),
    answers: T.arrayOf(T.shape({
      // TODO : answer propTypes
    }))
  },

  defaultProps: {
    finished: false,
    answers: []
  }
}

export {
  Paper
}
