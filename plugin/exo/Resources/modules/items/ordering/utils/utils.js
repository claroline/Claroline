import {SCORE_SUM} from './../../../quiz/enums'

export const utils = {
  answerIsValid(answer, solutions){
    const solution = solutions.find(solution => solution.itemId === answer.itemId)
    return undefined === solution.position ? false : solution.position === answer.position
  },
  showScore(answer, solutions) {
    return (utils.answerIsValid(answer, solutions) && solutions.find(solution => solution.itemId === answer.itemId).score > 0)
      || (!utils.answerIsValid(answer, solutions) && solutions.find(solution => solution.itemId === answer.itemId).score <= 0)
  },
  checkAllAnswers(solutions, answers) {
    const correctAnswers = solutions.filter(solution => solution.score > 0 && undefined !== answers.find(a => a.itemId === solution.itemId && a.position === solution.position))
    const correctSolutions = solutions.filter(solution => solution.score > 0)
    return correctAnswers.length === correctSolutions.length
  },
  getAnswerClass(answer, answers, solutions, scoreType) {
    const allAnswersValid = utils.checkAllAnswers(solutions, answers)
    if (scoreType === SCORE_SUM) {
      return utils.answerIsValid(answer, solutions) ? 'text-success positive-score' : 'text-danger negative-score'
    } else if (allAnswersValid) {
      return 'text-success positive-score'
    } else {
      // text belonging to right answers is in succes style but backgrounf is in error style
      return utils.answerIsValid(answer, solutions) ? 'text-success negative-score' : 'text-danger negative-score'
    }
  }
}
