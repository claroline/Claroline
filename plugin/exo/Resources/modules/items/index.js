import {checkPropTypes} from 'prop-types'

import {getApps} from '#/main/app/plugins'
import {ItemType, AnswerableItemType} from '#/plugin/exo/items/prop-types'

const APP_NAME = 'quizItems'

function loadDefinition(type) {
  const extendedType = type.default.answerable ? AnswerableItemType : ItemType

  // append some default values
  const defaultedType = Object.assign({}, extendedType.defaultProps, type.default)

  // validate type def
  checkPropTypes(extendedType.propTypes, defaultedType, 'prop', `ItemType<${defaultedType.name}>`)

  return defaultedType
}

/**
 * Gets all the item types registered in the application.
 *
 * @return {Promise.<Array>}
 */
function getItems(onlyEnabled = false) {
  // get all data types declared
  const itemTypes = getApps(APP_NAME)

  return Promise.all(
    // boot types applications
    Object.keys(itemTypes).map(type => itemTypes[type]())
  ).then(
    (loadedTypes) => loadedTypes.filter(type => !onlyEnabled || undefined === type.default.disabled || !type.default.disabled).map(loadDefinition)
  )
}

/**
 * Gets an item type definition by its name.
 *
 * @param {string} mimeType
 *
 * @return {Promise.<Object>}
 */
function getItem(mimeType) {
  return getItems().then(items => items.find(item => item.type === mimeType || mimeType.match(item.type)))
}

export {
  getItems,
  getItem
}
