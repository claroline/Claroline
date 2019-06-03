import merge from 'lodash/merge'
import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'
import {notBlank} from '#/main/core/validation'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/items/utils'
import {SelectionItem as SelectionItemType} from '#/plugin/exo/items/selection/prop-types'

import {validate as findValidate} from '#/plugin/exo/items/selection/editors/find'
import {validate as selectValidate} from '#/plugin/exo/items/selection/editors/select'
import {validate as highlightValidate} from '#/plugin/exo/items/selection/editors/highlight'

// components
import {SelectionEditor} from '#/plugin/exo/items/selection/components/editor'
import {SelectionPlayer} from '#/plugin/exo/items/selection/components/player'
import {SelectionPaper} from '#/plugin/exo/items/selection/components/paper'
import {SelectionFeedback} from '#/plugin/exo/items/selection/components/feedback'

// scores
import ScoreFixed from '#/plugin/exo/scores/fixed'
import ScoreSum from '#/plugin/exo/scores/sum'

function getCorrectedAnswerForSelectMode(item, corrected, answer = {data: {selections: []}}) {
  item.solutions.forEach(solution => {
    //user checked the answer
    if (answer.data && answer.data.selections.indexOf(solution.selectionId) >= 0) {
      solution.score > 0 ?
        corrected.addExpected(new Answerable(solution.score)):
        corrected.addUnexpected(new Answerable(solution.score))
      //the user didn't
    } else if (solution.score > 0) {
      corrected.addMissing(new Answerable(solution.score))
    }
  })

  return corrected
}

function getCorrectedAnswerForHighlightMode(item, corrected, answer = {data: {highlights: []}}) {
  item.solutions.forEach(solution => {
    const bestAnswer = solution.answers.reduce((prev, current) => prev.score > current.score ? prev : current)
    const userAnswer = answer.data ? answer.data.highlights.find(highlight => highlight.selectionId === solution.selectionId): null

    if (userAnswer) {
      if (userAnswer.colorId === bestAnswer.colorId) {
        corrected.addExpected(new Answerable(bestAnswer.score))
      } else {
        const userSolution = solution.answers.find(answer => answer.colorId === userAnswer.colorId)
        corrected.addUnexpected(new Answerable(userSolution.score))
        corrected.addMissing(new Answerable(bestAnswer.score))
      }
    } else {
      corrected.addMissing(new Answerable(bestAnswer.score))
      corrected.addPenalty(new Answerable(item.penalty))
    }
  })

  return corrected
}

function getCorrectedAnswerForFindMode(item, corrected, answer = {data:{positions: []}}) {
  let found = 0

  item.solutions.forEach(solution => {
    const positions = answer ? answer.data.positions: []
    const userAnswer = positions.find(position => position >= solution.begin && position <= solution.end)

    if (userAnswer) {
      found++
      solution.score > 0 ?
        corrected.addExpected(new Answerable(solution.score)):
        corrected.addUnexpected(new Answerable(solution.score))
    } else if (solution.score > 0) {
      corrected.addMissing(new Answerable(solution.score))
    }
  })

  const tries = answer && answer.data ? answer.data.tries: 0
  times(tries - found , () => corrected.addPenalty(new Answerable(item.penalty)))

  return corrected
}

export default {
  name: 'selection',
  type: 'application/x.selection+json',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  paper: SelectionPaper,
  player: SelectionPlayer,
  feedback: SelectionFeedback,

  components: {
    editor: SelectionEditor
  },

  supportScores: () => [ScoreFixed, ScoreSum],

  /**
   * Create a new selection item.
   *
   * @param {object} baseItem
   */
  create: (baseItem) => merge({}, baseItem, SelectionItemType.defaultProps),

  /**
   * Validate a selection item.
   *
   * @param {object} item
   *
   * @return {object} the list of item errors
   */
  validate: (item) => {
    let errors = {}

    switch (item.mode) {
      case 'find': {
        errors = Object.assign({}, errors, findValidate(item))
        break
      }
      case 'select': {
        errors = Object.assign({}, errors, selectValidate(item))
        break
      }
      case 'highlight': {
        errors = Object.assign({}, errors, highlightValidate(item))
      }
    }

    if (notBlank(item.text, {isHtml: true})) {
      errors.text = trans('selection_empty_text_error', {}, 'quiz')
    }

    if (!errors.text) {
      if (item.solutions.length === 0) {
        errors.text = trans('selection_text_must_contain_selections_error', {}, 'quiz')
      }
    }

    return errors
  },

  correctAnswer: (item, answer) => {
    const corrected = new CorrectedAnswer()

    switch (item.mode) {
      case 'select':
        return getCorrectedAnswerForSelectMode(item, corrected, answer)
      case 'find':
        return getCorrectedAnswerForFindMode(item, corrected, answer)
      case 'highlight':
        return getCorrectedAnswerForHighlightMode(item, corrected, answer)
    }
  },

  expectAnswer: (item) => {
    const answers = []

    if (item.solutions) {
      switch (item.mode) {
        case 'select':
        case 'find':
          item.solutions
            .filter(solution => 0 < solution.score)
            .map(solution => answers.push(new Answerable(solution.score)))

          break

        case 'highlight':
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
    }

    return answers
  },

  allAnswers: (item) => {
    if (item.solutions) {
      return item.solutions
        .filter(solution => 0 < solution.score)
        .map(solution => new Answerable(solution.score))
    }

    return []
  }
}
