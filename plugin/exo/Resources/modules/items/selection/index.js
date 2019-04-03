import merge from 'lodash/merge'
import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'
import {SelectionItem as SelectionItemType} from '#/plugin/exo/items/selection/prop-types'

// components
import {SelectionEditor} from '#/plugin/exo/items/selection/components/editor'
import {SelectionPlayer} from '#/plugin/exo/items/selection/components/player'
import {SelectionPaper} from '#/plugin/exo/items/selection/components/paper'
import {SelectionFeedback} from '#/plugin/exo/items/selection/components/feedback'

// scores
import ScoreFixed from '#/plugin/exo/scores/fixed'
import ScoreSum from '#/plugin/exo/scores/sum'

function getCorrectedAnswer(item, answer) {
  const corrected = new CorrectedAnswer()

  switch (item.mode) {
    case 'select':
      return getCorrectedAnswerForSelectMode(item, corrected, answer)
    case 'find':
      return getCorrectedAnswerForFindMode(item, corrected, answer)
    case 'highlight':
      return getCorrectedAnswerForHighlightMode(item, corrected, answer)
  }
}

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

  paper: SelectionPaper,
  player: SelectionPlayer,
  feedback: SelectionFeedback,
  getCorrectedAnswer
}
