import {PropTypes as T} from 'prop-types'

import {constants as quizConstants} from '#/plugin/exo/resources/quiz/constants'

const ChoiceItem = {
  propTypes: {
    choices: T.arrayOf(T.shape({
      // TODO : content prop-types (defined like this in JSON schemas)
      id: T.string.isRequired
    })).isRequired,
    solutions: T.arrayOf(T.shape({
      id: T.string.isRequired, // the id of the linked choice
      score: T.number,
      feedback: T.string
    })),
    numbering: T.oneOf(Object.keys(quizConstants.QUIZ_NUMBERINGS)),
    multiple: T.bool.isRequired,
    random: T.bool.isRequired,
    direction: T.oneOf(['vertical', 'horizontal'])
  },
  defaultProps: {
    choices: [],
    solutions: [],
    numbering: quizConstants.NUMBERING_NONE,
    multiple: false,
    random: false,
    direction: 'vertical'
  }
}

export {
  ChoiceItem
}
