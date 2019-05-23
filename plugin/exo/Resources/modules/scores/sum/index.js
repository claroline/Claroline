import {trans} from '#/main/app/intl/translation'

const SCORE_SUM = 'sum'

export default {
  name: SCORE_SUM,
  meta: {
    label: trans('score_sum', {}, 'quiz'),
    description: trans('score_sum_desc', {}, 'quiz')
  },

  hasAnswerScores: true,
  configure: (score, update) => [
    {
      name: '_roundScore',
      label: trans('round_total_score', {}, 'quiz'),
      type: 'boolean',
      calculated: score.total || score._roundScore,
      onChange: (checked) => {
        if (!checked) {
          update('total', null)
        }
      },
      linked: [
        {
          name: 'total',
          label: trans('total_score', {}, 'quiz'),
          type: 'number',
          required: true,
          displayed: score.total || score._roundScore,
          options: {
            min: 0
          }
        }
      ]
    }
  ],

  calculate: (scoreRule, correctedAnswer) => {
    let score = 0

    correctedAnswer.getExpected().forEach(el => score += el.getScore())
    correctedAnswer.getUnexpected().forEach(el => score += el.getScore())

    correctedAnswer.getPenalties().forEach(el => score -= el.getScore())

    if (scoreRule.total) {
      const total = []
        .concat(
          correctedAnswer.getExpected(),
          correctedAnswer.getMissing()
        )
        .reduce((totalScore, answerPart) => totalScore + answerPart.getScore(), 0)

      score = (score / total) * scoreRule.total
    }

    return score
  },

  calculateTotal: (scoreRule, expectedAnswer) => {
    if (scoreRule.total) {
      return scoreRule.total
    }

    return expectedAnswer.reduce((total, expected) => total + expected.getScore(), 0)
  }
}
