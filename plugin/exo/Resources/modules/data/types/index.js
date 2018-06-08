import {registerType} from '#/main/core/data'

import {SCORE_RULES_TYPE, scoreRulesDefinition} from '#/plugin/exo/data/types/score-rules'

function registerScoreRulesType() {
  registerType(SCORE_RULES_TYPE,  scoreRulesDefinition)
}

export {
  registerScoreRulesType
}
