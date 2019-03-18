import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

import {ChoiceItem} from '#/plugin/exo/items/choice/prop-types'

// components
import {ChoiceEditor} from '#/plugin/exo/items/choice/components/editor'
import {ChoiceFeedback} from '#/plugin/exo/items/choice/components/feedback'
import {ChoicePaper} from '#/plugin/exo/items/choice/components/paper'
import {ChoicePlayer} from '#/plugin/exo/items/choice/components/player'

// scores
import ScoreFixed from '#/plugin/exo/scores/fixed'
import ScoreRules from '#/plugin/exo/scores/rules'
import ScoreSum from '#/plugin/exo/scores/sum'

export default {
  type: 'application/x.choice+json',
  name: 'choice',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  components: {
    editor: ChoiceEditor
  },

  supportScores: (item) => {
    const supportedScores = [
      ScoreFixed,
      ScoreSum,
    ]

    if (item.multiple) {
      supportedScores.push(ScoreRules)
    }

    return supportedScores
  },

  /**
   * Create a new choice item.
   *
   * @param {object} baseItem
   */
  create: (baseItem) => {
    // append default choice props
    const choiceItem = merge({}, ChoiceItem.defaultProps, baseItem)

    // create 2 empty choices
    const firstChoiceId = makeId()
    const secondChoiceId = makeId()

    choiceItem.choices = [
      {
        id: firstChoiceId,
        type: 'text/html',
        data: ''
      }, {
        id: secondChoiceId,
        type: 'text/html',
        data: ''
      }
    ]

    // create solutions for choices
    choiceItem.solutions = [
      {
        id: firstChoiceId,
        score: 1,
        feedback: ''
      }, {
        id: secondChoiceId,
        score: 0,
        feedback: ''
      }
    ]

    return choiceItem
  },

  // correctAnswer
  getCorrectedAnswer: (item, answers = null) => {
    const corrected = new CorrectedAnswer()

    item.solutions.forEach(choice => {
      const score = choice.score

      if (answers && answers.data && answers.data.indexOf(choice.id) > -1) {
        score > 0 ?
          corrected.addExpected(new Answerable(score)) :
          corrected.addUnexpected(new Answerable(score))
      } else {
        if (score > 0) {
          corrected.addMissing(new Answerable(score))
        } else {
          corrected.addExpectedMissing(new Answerable(score))
        }
      }
    })

    return corrected
  },

  generateStats: (item, papers, withAllPapers) => {
    const stats = {
      choices: {},
      unanswered: 0,
      total: 0
    }

    Object.values(papers).forEach(p => {
      if (withAllPapers || p.finished) {
        let total = 0
        let nbAnswered = 0
        // compute the number of times the item is present in the structure of the paper
        p.structure.steps.forEach(s => {
          s.items.forEach(i => {
            if (i.id === item.id) {
              ++total
              ++stats.total
            }
          })
        })
        // compute the number of times the item has been answered
        p.answers.forEach(a => {
          if (a.questionId === item.id && a.data) {
            ++nbAnswered
            a.data.forEach(d => {
              if (!stats.choices[d]) {
                stats.choices[d] = 0
              }
              ++stats.choices[d]
            })
          }
        })
        stats.unanswered += total - nbAnswered
      }
    })

    return stats
  },

  // old
  paper: ChoicePaper,
  player: ChoicePlayer,
  feedback: ChoiceFeedback
}
