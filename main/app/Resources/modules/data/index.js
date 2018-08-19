import {checkPropTypes} from 'prop-types'

import {getApps, getApp} from '#/main/app/plugins'
import {DataType} from '#/main/app/data/prop-types'

/**
 * Gets all the data types registered in the application.
 *
 * @return {Promise.<Array>}
 */
function getTypes() {
  // get all data types declared
  const dataTypes = getApps('data.types')

  return Promise.all(
    // boot types applications
    Object.keys(dataTypes).map(type => dataTypes[type]())
  ).then((loadedTypes) => loadedTypes
    .map(loadedType => {
      // append some default values
      const defaultedType = Object.assign({}, DataType.defaultProps, loadedType.dataType)

      // validate type def
      checkPropTypes(DataType.propTypes, defaultedType, 'prop', `DataType<${defaultedType.name}>`)

      return defaultedType
    })
  )
}

/**
 * Gets the data types that can be configured and dynamically added to a form.
 * (see User profile or ClacoForm resource)
 *
 * @return {Promise.<Array>}
 */
function getCreatableTypes() {
  return getTypes()
    .then(loadedTypes => loadedTypes.filter(type => type.meta.creatable))
}

/**
 * Gets a data type definition by its name.
 *
 * @param {string} typeName
 *
 * @return {Promise.<Object>}
 */
function getType(typeName) {
  // retrieve the data type application
  const dataType = getApp('data.types', typeName)

  return dataType()
    .then((loadedType) => {
      // append some default values
      const defaultedType = Object.assign({}, DataType.defaultProps, loadedType.dataType)

      // validate type def
      checkPropTypes(DataType.propTypes, defaultedType, 'prop', `DataType<${defaultedType.name}>`)

      return defaultedType
    })
}

/**
 * Gets a data type definition by its name.
 *
 * @param {string} typeName
 *
 * @return {Promise.<Object>}
 */
function getSource(typeName) {
  // retrieve the data source application
  return getApp('data.sources', typeName)()
}

export {
  getCreatableTypes,
  getTypes,
  getType,
  getSource
}
