import invariant from 'invariant'

import textContent from './text'
import imageContent from './image'
import audioContent from './audio'
import videoContent from './video'

let registeredContentTypes = {}
let defaultRegistered = false

export function registerContentItemType(definition) {
  assertValidItemType(definition)

  if (registeredContentTypes[definition.type]) {
    throw new Error(`${definition.type} is already registered`)
  }

  definition.content = typeof definition.content !== 'undefined' ?
    definition.content :
    true

  registeredContentTypes[definition.type] = definition
}

export function registerDefaultContentItemTypes() {
  if (!defaultRegistered) {
    [textContent, imageContent, audioContent, videoContent].forEach(registerContentItemType)
    defaultRegistered = true
  }
}

export function listContentTypes() {
  return Object.keys(registeredContentTypes)
}

export function getContentDefinition(type) {
  const pattern = /^([^/]+)(\/[^/]+)?$/
  const matches = type.match(pattern)

  if (matches && matches[1]) {
    return registeredContentTypes[matches[1]]
  } else {
    throw new Error(`Unknown content type ${type}`)
  }
}


function assertValidItemType(definition) {
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
}

function makeError(message, definition) {
  const name = definition.name ? definition.name.toString() : '[unnamed]'

  return `${message} in '${name}' definition`
}
