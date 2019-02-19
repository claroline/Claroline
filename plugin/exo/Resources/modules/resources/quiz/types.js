import {trans} from '#/main/app/intl/translation'

/**
 * Declares all available type of quiz.
 *
 * Each type defines some default values for the quiz parameters.
 * It also allows to disable and hide some editor properties
 */

export const QUIZ_CONCEPTUALIZATION = 'conceptualization'
export const QUIZ_FORMATIVE         = 'formative'
export const QUIZ_SUMMATIVE         = 'summative'
export const QUIZ_CERTIFICATIVE     = 'certificative'
export const QUIZ_SURVEY            = 'survey'
export const QUIZ_CUSTOM            = 'custom'

export const QUIZ_TYPE_DEFAULT = QUIZ_CUSTOM

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
  presets: {
    hiddenProps: [],
    disabledProps: [],
    defaultProps: {}
  }
}

/**
 * Type : Formative
 */
const formativeType = {
  name: QUIZ_FORMATIVE,
  meta: {
    icon: 'fa fa-fw fa-graduation-cap',
    label: trans('quiz_formative', {}, 'quiz'),
    description: trans('quiz_formative_desc', {}, 'quiz')
  },
  presets: {
    hiddenProps: [],
    disabledProps: [],
    defaultProps: {}
  }
}

/**
 * Type : Summative
 */
const summativeType = {
  name: QUIZ_SUMMATIVE,
  meta: {
    icon: 'fa fa-fw',
    label: trans('quiz_summative', {}, 'quiz'),
    description: trans('quiz_summative_desc', {}, 'quiz')
  },
  presets: {
    hiddenProps: [],
    disabledProps: [],
    defaultProps: {}
  }
}

/**
 * Type : Summative
 */
const certificativeType = {
  name: QUIZ_CERTIFICATIVE,
  meta: {
    icon: 'fa fa-fw fa-award',
    label: trans('quiz_certificative', {}, 'quiz'),
    description: trans('quiz_certificative_desc', {}, 'quiz')
  },
  presets: {
    hiddenProps: [],
    disabledProps: [],
    defaultProps: {}
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
  presets: {
    hiddenProps: [],
    disabledProps: [],
    defaultProps: {}
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
  presets: {
    hiddenProps: [],
    disabledProps: [],
    defaultProps: {}
  }
}

export const QUIZ_TYPES = {
  [QUIZ_CONCEPTUALIZATION]: conceptualizationType,
  [QUIZ_FORMATIVE]        : formativeType,
  [QUIZ_SUMMATIVE]        : summativeType,
  [QUIZ_CERTIFICATIVE]    : certificativeType,
  [QUIZ_SURVEY]           : surveyType,
  [QUIZ_CUSTOM]           : customType
}
