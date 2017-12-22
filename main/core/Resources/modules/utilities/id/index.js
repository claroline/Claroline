import uuid from 'uuid'
import isArray from 'lodash/isArray'
import isObject from 'lodash/isObject'

// counter for id generation (test purpose)
let lastGeneratedIds = []

// generate a temporary id string
function makeId() {
  lastGeneratedIds.push(uuid())

  return lastGeneratedIds[lastGeneratedIds.length - 1]
}

// refresh all the ids string in an object or an array of object recursively and all
// the references it found
// this is a pretty heavy operation so try to not do it too many times
function refreshIds(object) {
  const idMap = {}
  mapNewIds(idMap, object)
  let serialized = JSON.stringify(object)
  Object.keys(idMap).forEach(key => {
    serialized = serialized.replace(new RegExp(key, 'g'), idMap[key])
  })
  object = JSON.parse(serialized)
  return object
}

function mapNewIds(idMap, object) {
  if (isArray(object)) {
    object.forEach(element => mapNewIds(idMap, element))
  } else {
    if (isObject(object)) {
      if (object.id && !idMap[object.id] && !Number.isInteger(object.id)) idMap[object.id] = makeId()
      Object.keys(object).forEach(key => mapNewIds(idMap, object[key]))
    }
  }

  return idMap
}

// test purpose only
function lastId() {
  return lastGeneratedIds[lastGeneratedIds.length - 1]
}

function lastIds(count) {
  if (count > lastGeneratedIds.length) {
    throw new Error(
      `Cannot access last ${count} ids, only ${lastGeneratedIds.length} were generated`
    )
  }

  const ids = []

  for (let i = lastGeneratedIds.length - count; i < lastGeneratedIds.length; ++i) {
    ids.push(lastGeneratedIds[i])
  }

  return ids
}

export {
  makeId,
  refreshIds,
  lastId,
  lastIds
}
