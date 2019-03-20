import {PropTypes as T} from 'prop-types'

import {
  QUIZ_PICKING_DEFAULT,
  SHUFFLE_NEVER,
  SHOW_CORRECTION_AT_VALIDATION,
  SHOW_SCORE_AT_CORRECTION
} from '#/plugin/exo/quiz/enums'

import {QUIZ_TYPE_DEFAULT} from '#/plugin/exo/resources/quiz/types'

const Step = {
  propTypes: {
    id: T.string.isRequired,
    title: T.string,
    description: T.string,
    parameters: T.shape({
      duration: T.number,
      maxAttempts: T.number
    }),
    picking: T.shape({
      pick: T.number,
      randomOrder: T.string,
      randomPick: T.string
    }),
    items: T.arrayOf(T.shape({
      // TODO : item types
    }))
  },
  defaultProps: {
    picking: {
      pick: 0,
      randomOrder: SHUFFLE_NEVER,
      randomPick: SHUFFLE_NEVER
    },
    parameters: {
      duration: 0,
      maxAttempts: 0
    }
  }
}

const Quiz = {
  propTypes: {
    id: T.string.isRequired,
    meta: T.shape({

    }),
    parameters: T.shape({
      type: T.string.isRequired
    }),
    picking: T.shape({

    }),
    steps: T.arrayOf(T.shape(
      Step.propTypes
    ))
  },
  defaultProps: {
    description: '',
    meta: {

    },
    parameters: {
      type: QUIZ_TYPE_DEFAULT,
      showMetadata: true,
      duration: 0,
      maxAttempts: 0,
      maxAttemptsPerDay: 0,
      mandatoryQuestions: false,
      maxPapers: 0,
      interruptible: false,
      showCorrectionAt: SHOW_CORRECTION_AT_VALIDATION,
      correctionDate: '',
      anonymizeAttempts: false,
      showScoreAt: SHOW_SCORE_AT_CORRECTION,
      showStatistics: false,
      showFullCorrection: true,
      showEndConfirm: true,
      endMessage: ''
    },
    picking: {
      type: QUIZ_PICKING_DEFAULT,
      pick: 0,
      randomOrder: SHUFFLE_NEVER,
      randomPick: SHUFFLE_NEVER
    },
    steps: []
  }
}

export {
  Quiz,
  Step
}
