import isEmpty from 'lodash/isEmpty'
import {chain, array, string, notBlank, lengthInRange} from '#/main/core/validation'

import {ScoreRulesGroup} from '#/plugin/exo/data/types/score-rules/components/form-group.jsx'

const SCORE_RULES_TYPE = 'score_rules'

const scoreRulesDefinition = {
  meta: {
    type: SCORE_RULES_TYPE
  },

  validate: (value, options) => chain(value, options, [array, lengthInRange, (value) => {
    if (value) {
      const errors = {}

      value.map((rule, index) => {
        const error = {}
        let hasError = false

        const typeError = chain(rule.type, [string, notBlank])
        const sourceError = chain(rule.source, [string, notBlank])
        const targetError = chain(rule.target, [string, notBlank])
        const pointsError = chain(rule.points, [notBlank])

        if (typeError) {
          error['type'] = typeError
          hasError = true
        }
        if (sourceError) {
          error['source'] = sourceError
          hasError = true
        }
        if (targetError) {
          error['target'] = targetError
          hasError = true
        }
        if (pointsError) {
          error['points'] = pointsError
          hasError = true
        }
        if (hasError) {
          errors[index] = error
        }
      })

      if (!isEmpty(errors)) {
        return errors
      }
    }
  }]),

  components: {
    form: ScoreRulesGroup
  }
}

export {
  SCORE_RULES_TYPE,
  scoreRulesDefinition
}
