import {tex} from '#/main/core/translation'

export const utils = {
  getAnswerClass(solution, answer) {
    if (answer === ''){
      return null
    } else if (solution.score > 0) {
      return answer === solution.id ? 'correct-answer' : null
    } else if (solution.score < 1) {
      return answer === solution.id ? 'incorrect-answer' : null
    }
  },

  getDefaultPairs() {
    return [
      {
        'id': '1',
        'labelA': tex('boolean_pair_true'),
        'labelB': tex('boolean_pair_false')
      },
      {
        'id': '2',
        'labelA': tex('boolean_pair_yes'),
        'labelB': tex('boolean_pair_no')
      }
    ]
  }
}
