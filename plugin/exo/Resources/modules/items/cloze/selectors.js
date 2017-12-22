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
  if (!answerText || 0 === answerText.length) {
    return undefined
  }

  return solutions.find(answer => {
    return (answer.caseSensitive && answer.text === answerText) // case sensitive
      || (!answer.caseSensitive && answer.text.toLowerCase() === answerText.toLowerCase()) // case insensitive
  })
}

export const select = {
  getHoleSolution,
  getAnswerSolution,
  getBestAnswer
}
