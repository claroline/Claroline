import invariant from 'invariant'
import mapValues from 'lodash/mapValues'

import choice from '#/plugin/exo/items/choice'
import match from '#/plugin/exo/items/match'
import cloze from '#/plugin/exo/items/cloze'
import selection from '#/plugin/exo/items/selection'
import graphic from '#/plugin/exo/items/graphic'
import open from '#/plugin/exo/items/open'
import pair from '#/plugin/exo/items/pair'
import words from '#/plugin/exo/items/words'
import set from '#/plugin/exo/items/set'
import grid from '#/plugin/exo/items/grid'
import ordering from '#/plugin/exo/items/ordering'


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

  //definition.editor.decorate = getOptionalFunction(definition.editor, 'decorate', item => item)
  //definition.editor.validate = getOptionalFunction(definition.editor, 'validate', () => ({}))

  registeredTypes[definition.type] = definition
}

export function registerDefaultItemTypes() {
  if (!defaultRegistered) {
    [choice, match, cloze, graphic, open, pair, words, set, grid, ordering, selection].forEach(registerItemType)
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
    definition.player,
    makeError('player component is mandatory', definition)
  )
  invariant(
    definition.paper,
    makeError('paper component is mandatory', definition)
  )
}

function makeError(message, definition) {
  const name = definition.name ? definition.name.toString() : '[unnamed]'

  return `${message} in '${name}' definition`
}
