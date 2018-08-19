import mapValues from 'lodash/mapValues'
import cloneDeep from 'lodash/cloneDeep'
import defaultsDeep from 'lodash/defaultsDeep'
import defaults from './defaults'
import {isQuestionType} from './../items/item-types'
import {tex} from '#/main/core/translation'

// augment normalized quiz data with editor state attributes and default values
// (can be passed an array of sub-decorators for each item mime type)
export function decorate(state, itemDecorators = {}, applyOnItems = true) {
  const newState = cloneDeep(state)

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
    })
  })
}

export function decorateItem(item, subDecorator = item => item) {
  let decorated = defaultsDeep(item, defaults.item)

  decorated.hints = decorated.hints.map(hint =>
    defaultsDeep(hint, defaults.hint)
  )

  return subDecorator(decorated)
}

export function formatQuizForTimer(data) {
  const newData = cloneDeep(data)

  if (newData['parameters']['timeLimited']) {
    newData['parameters']['showOverview'] = true
    newData['steps'].forEach(step => step.items.forEach(item => item['meta']['mandatory'] = false))
  }

  return newData
}
