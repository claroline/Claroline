import {PropTypes as T} from 'prop-types'

import {SCORE_MANUAL} from '#/plugin/exo/quiz/enums'

const OpenItem = {
  propTypes: {
    contentType: T.string.isRequired,
    score: T.shape({
      type: T.string.isRequired,
      max: T.number.isRequired
    }),
    solutions: T.array,
    maxScore: T.bool.isRequired,
    maxLength: T.number.isRequired
  },
  defaultProps: {
    contentType: 'text',
    score: {
      type: SCORE_MANUAL,
      max: 0
    },
    maxLength: 0,
    solutions: []
  }
}

export {
  OpenItem
}
