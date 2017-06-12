import invariant from 'invariant'
import difference from 'lodash/difference'
import mapValues from 'lodash/mapValues'

import choice from './choice'
import match from './match'
import cloze from './cloze'
import selection from './selection'
import graphic from './graphic'
import open from './open'
import pair from './pair'
import words from './words'
import set from './set'
import grid from './grid'
import ordering from './ordering'
import boolean from './boolean'

const typeProperties = [
  'name',
  'type',
  'question',
  'editor',
  'player',
  'feedback',
  'decorate',
  'validate',
  'paper',
  'getCorrectedAnswer'
]

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

  definition.editor.decorate = getOptionalFunction(definition.editor, 'decorate', item => item)
  definition.editor.validate = getOptionalFunction(definition.editor, 'validate', () => ({}))

  registeredTypes[definition.type] = definition
}

export function registerDefaultItemTypes() {
  if (!defaultRegistered) {
    [choice, match, cloze, graphic, open, pair, words, set, grid, ordering, boolean, selection].forEach(registerItemType)
    defaultRegistered = true
  }
}

export function listItemMimeTypes() {
  return Object.keys(registeredTypes)
}

export function listItemNames() {
  let list = []
  for(const type in registeredTypes){
    list.push({
      type:type,
      name:registeredTypes[type].name
    })
  }
  return list
}

export function getDefinition(type) {
  if (!registeredTypes[type]) {
    throw new Error(`Unknown item type ${type}`)
  }

  return registeredTypes[type]
}

export function getDecorators() {
  return mapValues(registeredTypes, eType => eType.editor.decorate, pType => pType.player.decorate)
}

// testing purposes only
export function resetTypes() {
  registeredTypes = {}
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
  invariant(
    definition.editor,
    makeError('editor is mandatory', definition)
  )
  invariant(
    definition.editor.component,
    makeError('editor component is mandatory', definition)
  )
  invariant(
    definition.editor.reduce,
    makeError('editor reduce is mandatory', definition)
  )
  invariant(
    typeof definition.editor.reduce === 'function',
    makeError('editor reduce must be a function', definition)
  )
  invariant(
    definition.player,
    makeError('player component is mandatory', definition)
  )
  invariant(
    definition.paper,
    makeError('paper component is mandatory', definition)
  )

  const extraProperties = difference(Object.keys(definition), typeProperties)

  if (extraProperties.length > 0) {
    invariant(
      false,
      makeError(`unknown property '${extraProperties[0]}'`, definition)
    )
  }
}

function getOptionalFunction(definition, name, defaultFunc) {
  if (typeof definition[name] !== 'undefined') {
    invariant(
      typeof definition[name] === 'function',
      makeError(`${name} must be a function`, definition)
    )
    return definition[name]
  }
  return defaultFunc
}

function makeError(message, definition) {
  const name = definition.name ? definition.name.toString() : '[unnamed]'

  return `${message} in '${name}' definition`
}
