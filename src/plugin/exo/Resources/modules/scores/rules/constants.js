import {trans} from '#/main/app/intl/translation'

const RULE_TYPE_ALL = 'all'
const RULE_TYPE_MORE = 'more'
const RULE_TYPE_LESS = 'less'
const RULE_TYPE_BETWEEN = 'between'

const RULE_TYPES = {
  [RULE_TYPE_ALL]: trans('score_rule_all', {}, 'quiz'),
  [RULE_TYPE_MORE]: trans('score_rule_more_than', {}, 'quiz'),
  [RULE_TYPE_LESS]: trans('score_rule_less_than', {}, 'quiz'),
  [RULE_TYPE_BETWEEN]: trans('score_rule_between', {}, 'quiz')
}

const RULE_SOURCE_CORRECT = 'correct'
const RULE_SOURCE_INCORRECT = 'incorrect'

const RULE_SOURCES = {
  [RULE_SOURCE_CORRECT]: trans('score_rule_correct_answers', {}, 'quiz'),
  [RULE_SOURCE_INCORRECT]: trans('score_rule_incorrect_answers', {}, 'quiz')
}

const RULE_TARGET_GLOBAL = 'global'
const RULE_TARGET_ANSWER = 'answer'

const RULE_TARGETS_CORRECT = {
  [RULE_TARGET_GLOBAL]: trans('score_rule_global_score', {}, 'quiz'),
  [RULE_TARGET_ANSWER]: trans('score_rule_by_correct_answer', {}, 'quiz')
}

const RULE_TARGETS_INCORRECT = {
  [RULE_TARGET_GLOBAL]: trans('score_rule_global_score', {}, 'quiz'),
  [RULE_TARGET_ANSWER]: trans('score_rule_by_incorrect_answer', {}, 'quiz')
}

export const constants = {
  RULE_TYPES,
  RULE_TYPE_ALL,
  RULE_TYPE_MORE,
  RULE_TYPE_LESS,
  RULE_TYPE_BETWEEN,

  RULE_SOURCES,
  RULE_SOURCE_CORRECT,
  RULE_SOURCE_INCORRECT,

  RULE_TARGETS_CORRECT,
  RULE_TARGETS_INCORRECT,
  RULE_TARGET_GLOBAL,
  RULE_TARGET_ANSWER
}
