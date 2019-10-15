import merge from 'lodash/merge'
import sampleSize from 'lodash/sampleSize'
import shuffle from 'lodash/shuffle'

import {now} from '#/main/app/intl/date'
import {makeId} from '#/main/core/scaffolding/id'

import {Step} from '#/plugin/exo/resources/quiz/prop-types'
import {constants} from '#/plugin/exo/resources/quiz/constants'
import {calculateTotal} from '#/plugin/exo/resources/quiz/papers/score'

// TODO : apply shuffle on answer items

/**
 * Generate a new paper for a quiz.
 *
 * @param {object} quiz          - the quiz definition
 * @param {object} user          - the user for which we want to create an attempt
 * @param {object} previousPaper - the previous attempt of the user if any
 *
 * @returns {{number: number, anonymized: boolean, structure}}
 */
function generateAttempt(quiz, user, previousPaper = null) {
  const newPaper = {
    id: makeId(),
    finished: false,
    startDate: now(),
    endDate: null,
    user: user,
    number: previousPaper ? previousPaper.number + 1 : 1,
    anonymized: quiz.parameters.anonymizeAttempts,
    structure: generateStructure(quiz, previousPaper)
  }

  // dump paper total score
  newPaper.total = calculateTotal(newPaper)

  return newPaper
}

function generateStructure(quiz, previousPaper = null) {
  switch (quiz.picking.type) {
    case constants.QUIZ_PICKING_TAGS:
      return generateStructureByTags(quiz, previousPaper)
    case constants.QUIZ_PICKING_DEFAULT:
    default:
      return generateStructureBySteps(quiz, previousPaper)
  }
}

function generateStructureBySteps(quiz, previousPaper = null) {
  const picking = quiz.picking
  const previousStructure = getPreviousStructure(quiz, previousPaper)

  // Generate the list of step ids for the paper
  let pickedSteps
  if (previousPaper && constants.SHUFFLE_ONCE === picking.randomPick) {
    // Get picked steps from the last user paper
    pickedSteps = previousStructure.steps.slice(0)
  } else {
    // Pick a new set of steps
    pickedSteps = pick(quiz.steps, picking.pick)
  }

  // Shuffles steps if needed
  if ( (!previousPaper && constants.SHUFFLE_ONCE === picking.randomOrder)
    || constants.SHUFFLE_ALWAYS === picking.randomOrder) {
    pickedSteps = shuffle(pickedSteps)
  }

  // Pick questions for each steps and generate structure
  return Object.assign({}, quiz, {
    steps: pickedSteps.map((pickedStep) => {
      let pickedItems = []

      const stepStructure = previousPaper ? previousStructure.find((step) => step.id === pickedStep.id) : null
      if (stepStructure && constants.SHUFFLE_ONCE === pickedStep.picking.randomPick) {
        // Get picked items from the last user paper
        // Retrieves the list of items of the current step
        pickedItems = stepStructure.items.slice(0)
      } else {
        // Pick a new set of questions
        pickedItems = pick(pickedStep.items, pickedStep.picking.pick)
      }

      // Shuffles items if needed
      if ( (!previousPaper && constants.SHUFFLE_ONCE === pickedStep.picking.randomOrder)
        || constants.SHUFFLE_ALWAYS === pickedStep.picking.randomOrder) {
        pickedItems = shuffle(pickedItems)
      }

      return Object.assign({}, pickedStep, {
        items: pickedItems
      })
    })
  })
}

function generateStructureByTags(quiz, previousPaper = null) {
  const picking = quiz.picking
  const previousStructure = getPreviousStructure(quiz, previousPaper)

  // Generate the list of step ids for the paper
  let pickedItems = []
  if (previousPaper && constants.SHUFFLE_ONCE === picking.randomPick) {
    // Get picked steps from the last user paper
    previousStructure.steps.map(step => {
      pickedItems = pickedItems.concat(step.items || [])
    })
  } else {
    // Pick a new set of items
    // Only pick wanted tags (format : ['tagName', itemCount])
    picking.pick.map(pickedTag => {
      const taggedItems = pick(
        quiz.steps.reduce((items, step) => {
          if (step.items) {
            return items.concat(step.items.filter(item => item.tags && -1 !== item.tags.indexOf(pickedTag[0])))
          }

          return items
        }, []),
        pickedTag[1]
      )

      pickedItems = pickedItems.concat(taggedItems)
    })
  }

  // Shuffle items according to config
  if ( (!previousPaper && constants.SHUFFLE_ONCE === picking.randomOrder)
    || constants.SHUFFLE_ALWAYS === picking.randomOrder) {
    pickedItems = shuffle(pickedItems)
  }

  // Create steps and fill it with the correct number of questions
  let pickedSteps = []
  while (0 < pickedItems.length) {
    const pickedStep = merge({}, Step.defaultProps, {
      id: makeId(),
      items: pickedItems.splice(0, picking.pageSize)
    })

    pickedSteps.push(pickedStep)
  }

  return Object.assign({}, quiz, {
    steps: pickedSteps
  })
}

function getPreviousStructure(quiz, previousPaper = null) {
  // The structure of the previous paper if any
  let previousStructure
  if (previousPaper) {
    previousStructure = Object.assign({}, previousPaper.structure)
  } else {
    previousStructure = Object.assign({}, quiz)
  }

  return previousStructure
}

/**
 * Picks a random subset of elements in a collection.
 * If count is 0, the whole collection is returned.
 *
 * @param {Array} originalSet
 * @param {number} count
 *
 * @returns {array}
 */
function pick(originalSet, count = 0) {
  let picked
  if (0 !== count) {
    // Get a random subset of element
    picked = sampleSize(originalSet, count).sort((a, b) => {
      // We need to put the picked items in their original order
      if (originalSet.indexOf(a) < originalSet.indexOf(b)) {
        return -1
      } else if (originalSet.indexOf(a) > originalSet.indexOf(b)) {
        return 1
      }
      return 0
    })
  } else {
    picked = originalSet.slice(0)
  }

  return picked
}

export {
  generateAttempt
}
