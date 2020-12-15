import invariant from 'invariant'

import textContent from '#/plugin/exo/contents/text'
import imageContent from '#/plugin/exo/contents/image'
import audioContent from '#/plugin/exo/contents/audio'
import videoContent from '#/plugin/exo/contents/video'
import resourceContent from '#/plugin/exo/contents/resource'

let registeredContentTypes = {}
let defaultRegistered = false

function registerContentItemType(definition) {
  assertValidItemType(definition)

  if (registeredContentTypes[definition.type]) {
    throw new Error(`${definition.type} is already registered`)
  }

  definition.content = typeof definition.content !== 'undefined' ?
    definition.content :
    true

  registeredContentTypes[definition.type] = definition
}

function registerDefaultContentItemTypes() {
  if (!defaultRegistered) {
    [textContent, imageContent, audioContent, videoContent, resourceContent].forEach(registerContentItemType)
    defaultRegistered = true
  }
}

function listContentTypes() {
  return Object.keys(registeredContentTypes)
}

function getContentDefinition(type) {
  const pattern = /^([^/]+)(\/[^/]+)?$/
  const matches = type.match(pattern)

  if (matches && matches[1]) {
    return Object.values(registeredContentTypes).find(registeredType => registeredType.name === matches[1])
  } else {
    throw new Error(`Unknown content type ${type}`)
  }
}

function isEditableType(type) {
  return 'text/html' === type
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

export {
  registerContentItemType,
  registerDefaultContentItemTypes,
  listContentTypes,
  getContentDefinition,
  isEditableType
}