import {getDefinition} from '#/plugin/exo/items/item-types'
import {Answerable} from '#/plugin/exo/items/utils'
import {
  calculateScore as calculateRuleScore,
  calculateTotal as calculateRuleTotal
} from '#/plugin/exo/scores'

/**
 * Calculate the score obtained by answer to an item.
 *
 * @param {object|Item.propTypes} item
 * @param {object}                answer
 * @param {boolean}               applyHints
 *
 * @return {number|null}
 */
function calculateScore(item, answer, applyHints = true) {
  const definition = getDefinition(item.type) // TODO : use new registry

  // only calculate score for answerable items
  if (definition && definition.answerable) {
    // let the item type correct the answer
    const correctedAnswer = definition.correctAnswer(item, answer)

    if (applyHints && answer.usedHints) {
      answer.usedHints.map(hint => {
        if (hint.penalty) {
          correctedAnswer.addPenalty(new Answerable(hint.penalty))
        }
      })
    }

    return calculateRuleScore(item.score, correctedAnswer)
  }

  return null
}

/**
 * Calculate the total score of an item.
 *
 * @param {object|Item.propTypes} item
 *
 * @return {number|null}
 */
function calculateTotal(item) {
  const definition = getDefinition(item.type) // TODO : use new registry

  // only calculate score for answerable items
  if (definition && definition.answerable) {
    const expectedAnswer = definition.expectAnswer(item)
    const allAnswers = definition.allAnswers(item)

    return calculateRuleTotal(item.score, expectedAnswer, allAnswers)
  }

  return null
}

export {
  calculateScore,
  calculateTotal
}
