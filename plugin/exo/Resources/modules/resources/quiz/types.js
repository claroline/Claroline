import {trans} from '#/main/app/intl/translation'

/**
 * Declares all available types of quiz.
 *
 * Each type defines some default values for the quiz parameters.
 * It also allows to disable and hide some editor properties
 */

const QUIZ_CONCEPTUALIZATION = 'conceptualization'
const QUIZ_FORMATIVE         = 'formative'
const QUIZ_SUMMATIVE         = 'summative'
const QUIZ_CERTIFICATION     = 'evaluative'
const QUIZ_SURVEY            = 'survey'
const QUIZ_CUSTOM            = 'custom'

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
  disabledProps: [],
  defaultProps: {}
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
  disabledProps: [],
  defaultProps: {}
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
  disabledProps: [],
  defaultProps: {}
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
  hiddenProps: [],
  disabledProps: [],
  defaultProps: {}
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
  defaultProps: {}
}

const QUIZ_TYPES = {
  [QUIZ_CONCEPTUALIZATION]: conceptualizationType,
  [QUIZ_FORMATIVE]        : formativeType,
  [QUIZ_SUMMATIVE]        : summativeType,
  [QUIZ_CERTIFICATION]    : certificationType,
  [QUIZ_SURVEY]           : surveyType,
  [QUIZ_CUSTOM]           : customType
}

export {
  QUIZ_TYPES,

  QUIZ_CONCEPTUALIZATION,
  QUIZ_FORMATIVE,
  QUIZ_SUMMATIVE,
  QUIZ_CERTIFICATION,
  QUIZ_SURVEY,
  QUIZ_CUSTOM
}
