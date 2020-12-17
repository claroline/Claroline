import isEmpty from 'lodash/isEmpty'
import {makeId} from '#/main/core/scaffolding/id'

import {trans} from '#/main/app/intl/translation'
import {stripDiacritics} from '#/main/core/scaffolding/text'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/items/utils'
import {constants} from '#/plugin/exo/items/grid/constants'
import {keywords as keywordsUtils} from '#/plugin/exo/utils/keywords'

// components
import {GridEditor} from '#/plugin/exo/items/grid/components/editor'
import {GridPaper} from '#/plugin/exo/items/grid/components/paper'
import {GridPlayer} from '#/plugin/exo/items/grid/components/player'
import {GridFeedback} from '#/plugin/exo/items/grid/components/feedback'

// scores
import ScoreFixed from '#/plugin/exo/scores/fixed'
import ScoreSum from '#/plugin/exo/scores/sum'

import {GridItem as GridItemTypes} from '#/plugin/exo/items/grid/prop-types'

function getCorrectAnswerForSumCellsMode(item, answer = {data: []}) {
  const corrected = new CorrectedAnswer()

  item.solutions.forEach(solution => {
    let userDataAnswer =
     answer && answer.data ?
       answer.data.find(userSolution => userSolution.cellId === solution.cellId): null
    let bestAnswer = findSolutionExpectedAnswer(solution)
    let userAnswer = userDataAnswer ?
      solution.answers.find(
        answer => (
          answer.text === userDataAnswer.text ||
          (stripDiacritics(answer.text).toUpperCase() === stripDiacritics(userDataAnswer.text).toUpperCase() && ! answer.caseSensitive)
        )
      ):
      null

    if (userAnswer) {
      userAnswer.score > 0 ?
        corrected.addExpected(new Answerable(userAnswer.score)):
        corrected.addUnexpected(new Answerable(userAnswer.score))
    } else {
      corrected.addMissing(new Answerable(bestAnswer.score))
      corrected.addPenalty(new Answerable(item.penalty))
    }
  })

  return corrected
}

function getCorrectAnswerForColSumMode(item, answer = {data: []}) {
  const corrected = new CorrectedAnswer()

  for (var i = 0; i < item.cols; i++) {
    const cellIds = item.cells.filter(cell =>
      cell.coordinates[0] === i && item.solutions.findIndex(solution => solution.cellId === cell.id) > -1
    ).map(cell => cell.id)

    validateCellsAnswer(corrected, item, answer, cellIds, 'column')
  }

  return corrected
}

function getCorrectAnswerForRowSumMode(item, answer = {data: []}) {
  const corrected = new CorrectedAnswer()

  for (var i = 0; i < item.cols; i++) {
    const cellIds = item.cells.filter(cell =>
      cell.coordinates[1] === i && item.solutions.findIndex(solution => solution.cellId === cell.id) > -1
    ).map(cell => cell.id)

    validateCellsAnswer(corrected, item, answer, cellIds, 'row')
  }

  return corrected
}

function getCorrectAnswerForFixMode(item, answer = {data: []}) {
  const corrected = new CorrectedAnswer()

  validateCellsAnswer(corrected, item, answer, item.cells.map(cell => cell.id), 'fix')

  return corrected
}

//check if the answers give by the users are all correct for an array of cellIds
function validateCellsAnswer(corrected, item, answer, cellIds, mode) {
  const answers = answer && answer.data ? answer.data.filter(answer => cellIds.indexOf(answer.cellId) >= 0): []
  const solutions = item.solutions.find(solution => cellIds.indexOf(solution.cellId) >= 0)
  const score = solutions.answers[0].score
  let valid = true

  switch (mode) {
    case 'row':
      valid = answers.length === item.rows
      break
    case 'column':
      valid = answers.length === item.columns
      break
  }

  answers.forEach(answer => {
    const expected = item.solutions.find(solution => solution.cellId === answer.cellId).answers.find(answer => answer.expected)
    if (answer.text !== expected.text ||
      (stripDiacritics(answer.text).toUpperCase() !== stripDiacritics(expected.text).toUpperCase() && !expected.caseSensitive)
    ) {
      valid = false
    }
  })

  if (valid) {
    corrected.addExpected(new Answerable(score))
  } else {
    corrected.addPenalty(new Answerable(item.penalty))
    corrected.addMissing(new Answerable(score))
  }

  return corrected
}

function findSolutionExpectedAnswer(solution) {
  let best = false

  solution.answers.forEach(answer => {
    if (!best || best.score < answer.score) best = answer
  })

  return best
}

export default {
  name: 'grid',
  type: 'application/x.grid+json',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  paper: GridPaper,
  player: GridPlayer,
  feedback: GridFeedback,

  components: {
    editor: GridEditor
  },

  supportScores: () => [
    ScoreSum,
    ScoreFixed
  ],

  create: (item) => {
    return Object.assign(item, GridItemTypes.defaultProps)
  },

  /**
   * Validate a grid item.
   *
   * @param {object} item
   *
   * @return {object} the list of item errors
   */
  validate: (item) => {
    const errors = {}

    if (item.solutions.length === 0) {
      // no solution at all
      errors.solutions = trans('grid_at_least_one_solution', {}, 'quiz')
    } else {
      item.cells.forEach(cell => {
        const solution = item.solutions.find(solution => solution.cellId === cell.id)
        if (solution) {
          const cellErrors = {}

          const keywordsErrors = keywordsUtils.validate(solution.answers, 'sum' === item.score.type && constants.SUM_CELL === item.sumMode, cell._multiple ? 2 : 1)
          if (!isEmpty(keywordsErrors)) {
            cellErrors.keywords = keywordsErrors
          }

          if (!isEmpty(cellErrors)) {
            errors[cell.id] = cellErrors
          }
        }
      })
    }

    return errors
  },

  correctAnswer: (item, answer = {data: []}) => {
    if (item.score.type === 'fixed') {
      return getCorrectAnswerForFixMode(item, answer)
    }

    switch (item.sumMode) {
      case constants.SUM_CELL: {
        return getCorrectAnswerForSumCellsMode(item, answer)
      }
      case constants.SUM_ROW: {
        return getCorrectAnswerForRowSumMode(item, answer)
      }
      case constants.SUM_COL: {
        return getCorrectAnswerForColSumMode(item, answer)
      }
    }
  },

  expectAnswer: (item) => {
    const answers = []
    let expected, solution

    if (item.solutions) {
      switch (item.sumMode) {
        case constants.SUM_CELL: {
          item.solutions.map(solution => {
            let expected
            solution.answers.map(answer => {
              if (!expected || answer.score > expected.score) {
                expected = answer
              }
            })

            if (expected) {
              answers.push(new Answerable(expected.score))
            }
          })

          break
        }

        case constants.SUM_COL: {
          for (let i = 0; i < item.cols; i++) {
            expected = item.cells.find(cell => cell.coordinates[0] === i && cell.choices && 0 !== cell.choices.length)
            if (expected) {
              solution = item.solutions.find(solution => solution.cellId === expected.id)
              if (solution) {
                answers.push(new Answerable(solution.score))
              }

            }
          }

          break
        }

        case constants.SUM_ROW: {
          for (let i = 0; i < item.rows; i++) {
            expected = item.cells.find(cell => cell.coordinates[1] === i && cell.choices && 0 !== cell.choices.length)
            if (expected) {
              solution = item.solutions.find(solution => solution.cellId === expected.id)
              if (solution) {
                answers.push(new Answerable(solution.score))
              }

            }
          }

          break
        }
      }
    }

    return answers
  },

  allAnswers: (item) => {
    const answers = []

    if (item.solutions) {
      item.solutions.map(solution => solution.answers.map(answer => new Answerable(answer.score)))
    }

    return answers
  },

  refreshIdentifiers: (item) => {
    item.id = makeId()

    const mapIds = {}

    item.cells.forEach(cell => {
      mapIds[cell.id] = makeId()
      cell.id = mapIds[cell.id]
    })

    item.solutions.forEach(solution => {
      solution.cellId = mapIds[solution.cellId]
    })

    return item
  }
}
