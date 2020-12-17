import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'

import {constants} from '#/plugin/exo/resources/quiz/constants'

/**
 * Declares all available types of quiz.
 *
 * Each type defines some default values for the quiz parameters.
 * It also allows to disable and hide some editor properties.
 *
 * defaultProps : when changing the type of quiz, if the user have replaced one of the default props
 * it will not be overridden.
 *
 * requiredProps: when changing the type quiz, the user defined values will be overridden.
 */

const QUIZ_CONCEPTUALIZATION = 'conceptualization'
const QUIZ_FORMATIVE         = 'formative'
const QUIZ_SUMMATIVE         = 'summative'
const QUIZ_CERTIFICATION     = 'evaluative'
const QUIZ_SURVEY            = 'survey'
const QUIZ_CUSTOM            = 'custom'

const QUIZ_TYPE_DEFAULT = QUIZ_CUSTOM

/**
 * Type : Conceptualization
 */
const conceptualizationType = {
  name: QUIZ_CONCEPTUALIZATION,
  meta: {
    icon: 'fa fa-fw fa-brain',
    label: trans('quiz_conceptualization', {}, 'quiz'),
    description: trans('quiz_conceptualization_desc', {}, 'quiz')
  },
  hiddenProps: [],
  disabledProps: [],
  defaultProps: {}
}

/**
 * Type : Formative
 */
const formativeType = {
  name: QUIZ_FORMATIVE,
  meta: {
    icon: 'fa fa-fw fa-chart-line',
    label: trans('quiz_formative', {}, 'quiz'),
    description: trans('quiz_formative_desc', {}, 'quiz')
  },
  hiddenProps: [],
  disabledProps: [
    'parameters.hasExpectedAnswers'
  ],

  defaultProps: {},
  requiredProps: {
    parameters: {
      hasExpectedAnswers: true,
      showFeedback: true,
      showFullCorrection: true,
      showCorrectionAt: constants.QUIZ_RESULTS_AT_VALIDATION,
      showScoreAt: constants.QUIZ_SCORE_AT_CORRECTION
    },
    picking: {
      randomOrder: constants.SHUFFLE_ALWAYS,
      randomPick: constants.SHUFFLE_ALWAYS
    }
  }
}

/**
 * Type : Summative
 */
const summativeType = {
  name: QUIZ_SUMMATIVE,
  meta: {
    icon: 'fa fa-fw fa-award',
    label: trans('quiz_summative', {}, 'quiz'),
    description: trans('quiz_summative_desc', {}, 'quiz')
  },
  hiddenProps: [],
  disabledProps: [
    'parameters.hasExpectedAnswers'
  ],

  defaultProps: {},
  requiredProps: {
    parameters: {
      maxAttempts: 1,
      hasExpectedAnswers: true,
      showFeedback: false,
      showEndConfirm: true
    }
  }
}

/**
 * Type : Certification
 */
const certificationType = {
  name: QUIZ_CERTIFICATION,
  meta: {
    icon: 'fa fa-fw fa-graduation-cap',
    label: trans('quiz_certification', {}, 'quiz'),
    description: trans('quiz_certification_desc', {}, 'quiz')
  },

  hiddenProps: [],
  disabledProps: [
    'parameters.hasExpectedAnswers',
    'parameters.interruptible'
  ],

  defaultProps: {},
  requiredProps: {
    parameters: {
      maxAttempts: 1,
      hasExpectedAnswers: true,
      showFeedback: false,
      interruptible: false,
      showEndConfirm: true
    }
  }
}

/**
 * Type : Survey
 */
const surveyType = {
  name: QUIZ_SURVEY,
  meta: {
    icon: 'fa fa-fw fa-poll',
    label: trans('quiz_survey', {}, 'quiz'),
    description: trans('quiz_survey_desc', {}, 'quiz')
  },
  hiddenProps: [
    'parameters.hasExpectedAnswers'
  ],
  disabledProps: [
    'parameters.anonymizeAttempts'
  ],

  defaultProps: {
    parameters: {
      showCorrectionAt: constants.QUIZ_RESULTS_AT_NEVER
    }
  },
  requiredProps: {
    parameters: {
      maxAttempts: 1,
      anonymizeAttempts: true,
      hasExpectedAnswers: false,
      showFeedback: false,
      showStatistics: true,
      showCorrectionAt: constants.QUIZ_SCORE_AT_NEVER
    }
  }
}

/**
 * Type : Custom
 */
const customType = {
  name: QUIZ_CUSTOM,
  meta: {
    icon: 'fa fa-fw fa-question',
    label: trans('quiz_custom', {}, 'quiz'),
    description: trans('quiz_custom_desc', {}, 'quiz')
  },
  hiddenProps: [],
  disabledProps: [],
  defaultProps: {},
  requiredProps: {}
}

const QUIZ_TYPES = {
  [QUIZ_CONCEPTUALIZATION]: conceptualizationType,
  [QUIZ_FORMATIVE]        : formativeType,
  [QUIZ_SUMMATIVE]        : summativeType,
  [QUIZ_CERTIFICATION]    : certificationType,
  [QUIZ_SURVEY]           : surveyType,
  [QUIZ_CUSTOM]           : customType
}

function configureTypeFields(quizType, fields = []) {
  const typePresets = QUIZ_TYPES[quizType]

  return fields
    // hides fields
    .filter(field => -1 === typePresets.hiddenProps.indexOf(field.name))
    // disables fields
    .map(field => {
      field.disabled = -1 !== typePresets.disabledProps.indexOf(field.name) || field.disabled

      if (field.linked) {
        field.linked = configureTypeFields(quizType, field.linked)
      }

      return field
    })
}

/**
 * Applies type presets to the quiz form.
 * It disables and hides fields which are not related with the type.
 *
 * @param {string} quizType
 * @param {Array}  formDefinition
 *
 * @return {Array}
 */
function configureTypeEditor(quizType, formDefinition = []) {
  const updatedForm = cloneDeep(formDefinition)

  return updatedForm.map(section => {
    section.fields = configureTypeFields(quizType, section.fields)

    return section
  })
}

/**
 * Sets the quiz parameters based on the preset of a quiz type.
 *
 * @param {string} quizType
 * @param {object} quizData
 */
function setTypePresets(quizType, quizData) {
  const presets = QUIZ_TYPES[quizType]

  return merge({}, presets.defaultProps, quizData, {
    parameters: {type: quizType}
  }, presets.requiredProps)
}

export {
  QUIZ_TYPES,
  QUIZ_TYPE_DEFAULT,

  QUIZ_CONCEPTUALIZATION,
  QUIZ_FORMATIVE,
  QUIZ_SUMMATIVE,
  QUIZ_CERTIFICATION,
  QUIZ_SURVEY,
  QUIZ_CUSTOM,

  configureTypeEditor,
  setTypePresets
}
