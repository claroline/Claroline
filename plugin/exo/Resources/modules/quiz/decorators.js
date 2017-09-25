import mapValues from 'lodash/mapValues'
import cloneDeep from 'lodash/cloneDeep'
import defaultsDeep from 'lodash/defaultsDeep'
import defaults from './defaults'
import {TYPE_QUIZ} from './enums'
import {makeId} from './../utils/utils'
import {isQuestionType} from './../items/item-types'
import {tex} from '#/main/core/translation'

// augment normalized quiz data with editor state attributes and default values
// (can be passed an array of sub-decorators for each item mime type)
export function decorate(state, itemDecorators = {}, applyOnItems = true) {
  const newState = cloneDeep(state)

  // create an empty step if none
  if (newState.quiz.steps.length === 0) {
    const defaultStep = {
      id: makeId(),
      items:[]
    }
    newState.steps[defaultStep.id] = defaultStep
    newState.quiz.steps = [defaultStep.id]
  }

  newState.quiz.parameters.pickByTag = !!newState.quiz.parameters.randomTags.pageSize

  let stepIdx = 0

  return Object.assign(newState, {
    quiz: defaultsDeep(newState.quiz, defaults.quiz),
    steps: mapValues(newState.steps, (step) => {
      step = defaultsDeep(step, defaults.step)
      if (!step.title) {
        step.title = `${tex('step')} ${stepIdx + 1}`
        stepIdx++
      }

      return step
    }),
    items: mapValues(newState.items, item => {
      const subDecorator = itemDecorators[item.type] || (item => item)
      return applyOnItems && isQuestionType(item.type) ? decorateItem(item, subDecorator) : item
    }),
    editor: {
      currentObject: {
        id: newState.quiz.id,
        type: TYPE_QUIZ
      }
    }
  })
}

export function decorateItem(item, subDecorator = item => item) {
  let decorated = defaultsDeep(item, defaults.item)

  decorated.hints = decorated.hints.map(hint =>
    defaultsDeep(hint, defaults.hint)
  )

  return subDecorator(decorated)
}
