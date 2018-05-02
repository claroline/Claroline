import { stripDiacritics } from '#/main/core/scaffolding/text/strip-diacritics'

/**
 *
 * @param {object} hole
 * @param {Array}  solutions
 *
 * @return {object|undefined}
 */
const getHoleSolution = (hole, solutions) => solutions.find(solution => solution.holeId === hole.id)

/**
 * Gets the answer with the maximum score for a Hole.
 *
 * @param {Array} answers
 *
 * @returns {object|null}
 */
const getBestAnswer = (answers) => {
  let bestAnswer = null
  answers.map(answer => {
    if (!bestAnswer || (answer.score > bestAnswer.score && 0 < answer.score)) {
      bestAnswer = answer
    }
  })

  return bestAnswer
}

/**
 * Retrieves the solution associated to a given answer if any exists.
 *
 * @param {Array}  solutions
 * @param {string} answerText
 *
 * @returns {object|undefined}
 */
function getAnswerSolution(solutions, answerText) {
  if (!answerText || 0 === answerText.trim().length) {
    return undefined
  }
  answerText = answerText.trim()
  
  return solutions.find(answer => {
    return answer.text === answerText || // case sensitive
      (!answer.caseSensitive && stripDiacritics(answer.text).toUpperCase() === stripDiacritics(answerText).toUpperCase()) // case insensitive
  })
}

export const select = {
  getHoleSolution,
  getAnswerSolution,
  getBestAnswer
}
