import {PropTypes as T} from 'prop-types'

import {Quiz} from '#/plugin/exo/resources/quiz/prop-types'

const Answer = {
  propTypes: {
    id: T.string.isRequired,
    questionId: T.string.isRequired,
    tries: T.number,
    usedHints: T.array,
    feedback: T.string,
    score: T.string,
    data: T.any // type depends on the question type
  }
}

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
    answers: T.arrayOf(T.shape(
      Answer.propTypes
    ))
  },

  defaultProps: {
    finished: false,
    answers: []
  }
}

export {
  Paper,
  Answer
}
