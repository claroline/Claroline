import {tex} from '#/main/core/translation'

export const RULE_TYPE_ALL = 'all'
export const RULE_TYPE_MORE = 'more'
export const RULE_TYPE_LESS = 'less'
export const RULE_TYPE_BETWEEN = 'between'

export const ruleTypes = {
  [RULE_TYPE_ALL]: tex('score_rule_all'),
  [RULE_TYPE_MORE]: tex('score_rule_more_than'),
  [RULE_TYPE_LESS]: tex('score_rule_less_than'),
  [RULE_TYPE_BETWEEN]: tex('score_rule_between')
}

export const RULE_SOURCE_CORRECT = 'correct'
export const RULE_SOURCE_INCORRECT = 'incorrect'

export const ruleSources = {
  [RULE_SOURCE_CORRECT]: tex('score_rule_correct_answers'),
  [RULE_SOURCE_INCORRECT]: tex('score_rule_incorrect_answers')
}

export const RULE_TARGET_GLOBAL = 'global'
export const RULE_TARGET_ANSWER = 'answer'

export const ruleTargetsCorrect = {
  [RULE_TARGET_GLOBAL]: tex('score_rule_global_score'),
  [RULE_TARGET_ANSWER]: tex('score_rule_by_correct_answer')
}

export const ruleTargetsIncorrect = {
  [RULE_TARGET_GLOBAL]: tex('score_rule_global_score'),
  [RULE_TARGET_ANSWER]: tex('score_rule_by_incorrect_answer')
}