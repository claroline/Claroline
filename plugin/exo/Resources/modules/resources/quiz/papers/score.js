import {
  CorrectedAnswer,
  Answerable
} from '#/plugin/exo/items/utils'
import {
  calculateScore as calculateItemScore,
  calculateTotal as calculateItemTotal
} from '#/plugin/exo/items/score'
import {
  calculateScore as calculateRuleScore,
  calculateTotal as calculateRuleTotal
} from '#/plugin/exo/scores'

/**
 * Calculate the score of a paper.
 *
 * @param {object|Paper.propTypes} paper
 *
 * @return {number}
 */
function calculateScore(paper) {
  const corrected = new CorrectedAnswer()

  paper.structure.steps.map(step => {
    step.items.map(item => {
      if (item.type.match(/^application\/x\.[^/]+\+json$/)) { // not the best way to retrieve it
        const itemTotal = calculateItemTotal(item)

        // search answer for the item
        const answer = paper.answers.find(answer => answer.questionId === item.id)
        if (!answer) {
          // no answer found
          corrected.addMissing(new Answerable(itemTotal, item.id))
        } else {
          // get the answer score without hints
          // this is required to check if the item has been correctly answered
          // we don't want the use of an hint with penalty mark the question has incorrect
          // because this is how it works in item scores
          const itemScore = calculateItemScore(item, answer, false)
          if (itemTotal === itemScore) {
            // item is fully correct
            corrected.addExpected(new Answerable(calculateItemScore(item, answer), item.id))
          } else {
            corrected.addUnexpected(new Answerable(calculateItemScore(item, answer), item.id))
          }
        }
      }
    })
  })

  return calculateRuleScore(paper.structure.score, corrected)
}

/**
 * Calculate the total score of a paper.
 *
 * @param {object|Paper.propTypes} paper
 *
 * @return {number}
 */
function calculateTotal(paper) {
  const items = []
  paper.structure.steps.map(step => {
    step.items.map(item => {
      const itemTotal = calculateItemTotal(item)
      if (itemTotal) {
        items.push(new Answerable(itemTotal, item.id))
      }
    })
  })

  return calculateRuleTotal(paper.structure.score, items, items)
}

export {
  calculateScore,
  calculateTotal
}
