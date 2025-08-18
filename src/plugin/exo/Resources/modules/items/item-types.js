import invariant from 'invariant'

import choice from '#/plugin/exo/items/choice'
import match from '#/plugin/exo/items/match'
import cloze from '#/plugin/exo/items/cloze'
import graphic from '#/plugin/exo/items/graphic'
import open from '#/plugin/exo/items/open'
import pair from '#/plugin/exo/items/pair'
import words from '#/plugin/exo/items/words'
import set from '#/plugin/exo/items/set'
import grid from '#/plugin/exo/items/grid'
import ordering from '#/plugin/exo/items/ordering'
import waveform from '#/plugin/audio-player/quiz/items/waveform'

let registeredTypes = {}
let defaultRegistered = false

export function registerItemType(definition) {
  assertValidItemType(definition)

  if (registeredTypes[definition.type]) {
    throw new Error(`${definition.type} is already registered`)
  }

  definition.question = typeof definition.question !== 'undefined' ?
    definition.question :
    true

  registeredTypes[definition.type] = definition
}

export function registerDefaultItemTypes() {
  if (!defaultRegistered) {
    [choice, match, cloze, graphic, open, pair, words, set, grid, ordering, waveform].forEach(registerItemType)
    defaultRegistered = true
  }
}

/**
 * @deprecated use new registry
 */
export function getDefinition(type) {
  if (!registeredTypes[type]) {
    throw new Error(`Unknown item type ${type}`)
  }

  return registeredTypes[type]
}

export function getComponent(type, componentName) {
  const definition = getDefinition(type)

  return definition.components[componentName]
}

export function isQuestionType(type) {
  const matches = type.match(/^application\/x\.[^/]+\+json$/)

  return matches !== null
}

function assertValidItemType(definition) {
  invariant(
    definition.name,
    makeError('name is mandatory', definition)
  )
  invariant(
    typeof definition.name === 'string',
    makeError('name must be a string', definition)
  )
  invariant(
    definition.type,
    makeError('mime type is mandatory', definition)
  )
  invariant(
    typeof definition.type === 'string',
    makeError('mime type must be a string', definition)
  )
}

function makeError(message, definition) {
  const name = definition.name ? definition.name.toString() : '[unnamed]'

  return `${message} in '${name}' definition`
}
