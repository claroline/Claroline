import {PropTypes as T} from 'prop-types'

import {SCORE_MANUAL} from '#/plugin/exo/quiz/enums'

const OpenItem = {
  propTypes: {
    contentType: T.string.isRequired,
    solutions: T.array,
    maxLength: T.number
  },
  defaultProps: {
    contentType: 'text',
    score: {
      type: SCORE_MANUAL,
      max: 0
    },
    solutions: []
  }
}

export {
  OpenItem
}
