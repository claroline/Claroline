import {trans} from '#/main/app/intl/translation'

const CHOICE_TYPE_MULTIPLE = 'multiple'
const CHOICE_TYPE_SINGLE   = 'single'

const CHOICE_TYPES = {
  [CHOICE_TYPE_MULTIPLE]: trans('choice_single_answer', {}, 'quiz'),
  [CHOICE_TYPE_SINGLE]: trans('choice_multiple_answers', {}, 'quiz')
}

// TODO : move elsewhere

export const RULE_TYPE_ALL = 'all'
export const RULE_TYPE_MORE = 'more'
export const RULE_TYPE_LESS = 'less'
export const RULE_TYPE_BETWEEN = 'between'

export const ruleTypes = {
  [RULE_TYPE_ALL]: trans('score_rule_all', {}, 'quiz'),
  [RULE_TYPE_MORE]: trans('score_rule_more_than', {}, 'quiz'),
  [RULE_TYPE_LESS]: trans('score_rule_less_than', {}, 'quiz'),
  [RULE_TYPE_BETWEEN]: trans('score_rule_between', {}, 'quiz')
}

export const RULE_SOURCE_CORRECT = 'correct'
export const RULE_SOURCE_INCORRECT = 'incorrect'

export const ruleSources = {
  [RULE_SOURCE_CORRECT]: trans('score_rule_correct_answers', {}, 'quiz'),
  [RULE_SOURCE_INCORRECT]: trans('score_rule_incorrect_answers', {}, 'quiz')
}

export const RULE_TARGET_GLOBAL = 'global'
export const RULE_TARGET_ANSWER = 'answer'

export const ruleTargetsCorrect = {
  [RULE_TARGET_GLOBAL]: trans('score_rule_global_score', {}, 'quiz'),
  [RULE_TARGET_ANSWER]: trans('score_rule_by_correct_answer', {}, 'quiz')
}

export const ruleTargetsIncorrect = {
  [RULE_TARGET_GLOBAL]: trans('score_rule_global_score', {}, 'quiz'),
  [RULE_TARGET_ANSWER]: trans('score_rule_by_incorrect_answer', {}, 'quiz')
}

export const constants = {
  CHOICE_TYPE_MULTIPLE,
  CHOICE_TYPE_SINGLE,
  CHOICE_TYPES
}
