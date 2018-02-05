import invariant from 'invariant'

import {BOOLEAN_TYPE,     booleanDefinition}     from '#/main/core/data/types/boolean'
import {COLOR_TYPE,       colorDefinition}       from '#/main/core/data/types/color'
import {COUNTRY_TYPE,     countryDefinition}     from '#/main/core/data/types/country'
import {DATE_TYPE,        dateDefinition}        from '#/main/core/data/types/date'
import {DATE_RANGE_TYPE,  dateRangeDefinition}   from '#/main/core/data/types/date-range'
import {EMAIL_TYPE,       emailDefinition}       from '#/main/core/data/types/email'
import {ENUM_TYPE,        enumDefinition}        from '#/main/core/data/types/enum'
import {FILE_TYPE,        fileDefinition}        from '#/main/core/data/types/file'
import {HTML_TYPE,        htmlDefinition}        from '#/main/core/data/types/html'
import {IMAGE_TYPE,       imageDefinition}       from '#/main/core/data/types/image'
import {IP_TYPE,          ipDefinition}          from '#/main/core/data/types/ip'
import {LOCALE_TYPE,      localeDefinition}      from '#/main/core/data/types/locale'
import {NUMBER_TYPE,      numberDefinition}      from '#/main/core/data/types/number'
import {PASSWORD_TYPE,    passwordDefinition}    from '#/main/core/data/types/password'
import {SCORE_TYPE,       scoreDefinition}       from '#/main/core/data/types/score'
import {STRING_TYPE,      stringDefinition}      from '#/main/core/data/types/string'
import {TRANSLATION_TYPE, translationDefinition} from '#/main/core/data/types/translation'
import {USERNAME_TYPE,    usernameDefinition}    from '#/main/core/data/types/username'
import {TRANSLATED_TYPE, translatedDefinition}   from '#/main/core/data/types/translated'

// the list of registered data types
const dataTypes = {}

// register default types
registerType(BOOLEAN_TYPE,     booleanDefinition)
registerType(COLOR_TYPE,       colorDefinition)
registerType(COUNTRY_TYPE,     countryDefinition)
registerType(DATE_TYPE,        dateDefinition)
registerType(DATE_RANGE_TYPE,  dateRangeDefinition)
registerType(EMAIL_TYPE,       emailDefinition)
registerType(ENUM_TYPE,        enumDefinition)
registerType(FILE_TYPE,        fileDefinition)
registerType(IMAGE_TYPE,       imageDefinition)
registerType(HTML_TYPE,        htmlDefinition)
registerType(IP_TYPE,          ipDefinition)
registerType(LOCALE_TYPE,      localeDefinition)
registerType(NUMBER_TYPE,      numberDefinition)
registerType(PASSWORD_TYPE,    passwordDefinition)
registerType(SCORE_TYPE,       scoreDefinition)
registerType(STRING_TYPE,      stringDefinition)
registerType(TRANSLATION_TYPE, translationDefinition)
registerType(TRANSLATED_TYPE,  translatedDefinition)
registerType(USERNAME_TYPE,    usernameDefinition)

/**
 * Validates & registers a data type.
 *
 * @param {string} typeName
 * @param {Object} typeDefinition
 */
function registerType(typeName, typeDefinition) {
  invariant(typeof typeName === 'string', `Data type name must be a string. "${typeName}" provided.`)
  invariant(!dataTypes[typeName],         `Data type ${typeName} is already registered.`)

  const definition = setDefinitionDefaults(typeDefinition)
  validateDefinition(definition)

  // register the new type
  dataTypes[typeName] = definition
}

/**
 * Gets the list of registered data types.
 *
 * @returns {Object}
 */
function getTypes() {
  return dataTypes
}

function getCreatableTypes() {
  return Object.keys(dataTypes)
    .filter(type => dataTypes[type].meta.creatable)
    .reduce((creatableTypes, type) => {
      creatableTypes[type] = dataTypes[type]

      return creatableTypes
    }, {})
}

/**
 * Gets a registered data type by its name.
 *
 * @param typeName
 *
 * @returns {Object}
 */
function getType(typeName) {
  invariant(dataTypes[typeName], `Data type "${typeName}" is not registered.`)

  return dataTypes[typeName]
}

/**
 * Gets the default data type.
 *
 * @returns {Object}
 */
function getDefaultType() {
  return dataTypes[STRING_TYPE]
}

/**
 * Tries to get a type by its name and return the default one if not found.
 *
 * @param {string} typeName
 *
 * @returns {Object}
 */
function getTypeOrDefault(typeName) {
  try {
    return getType(typeName)
  } catch (e) {
    return getDefaultType()
  }
}

/**
 * Validates a data type definition.
 *
 * @param definition
 */
function validateDefinition(definition) {
  invariant(typeof definition === 'object', 'Data type definition must be an object.')

  invariant(typeof definition.parse === 'function',    'Data type "parse" property must be a function.')
  invariant(typeof definition.render === 'function',   'Data type "render" property must be a function.')
  invariant(typeof definition.validate === 'function', 'Data type "validate" property must be a function.')

  invariant(typeof definition.meta === 'object', 'Data type "meta" property must be a object.')
  invariant(typeof definition.meta.type === 'string', 'Data type "meta.type" property must be a string.')
  invariant(typeof definition.configure === 'function', 'Data type "configure" property must be a function.')

  if (definition.components) {
    invariant(typeof definition.components === 'object', 'Data type "components" property must be a object.')
  }
}

/**
 * Sets default values in a data type definition.
 * NB : this method does not mutate the original definition object.
 *
 * @param {object} definition
 *
 * @returns {object}
 */
function setDefinitionDefaults(definition) {
  return Object.assign({
    meta: {
      creatable: false
    },

    /**
     * The list of configuration fields for the data type.
     * It gets the current options values as param.
     *
     * @return {array}
     */
    configure: () => [],

    /**
     * Parses a value.
     *
     * @param value
     */
    parse: (value) => value,

    /**
     * Displays a value for the data type.
     *
     * @param raw
     */
    render: (raw) => raw,

    /**
     * Validates a value provided for the data type.
     */
    validate: () => undefined,

    /**
     * Custom components for the data type.
     *
     * Keys :
     *   - search
     *   - form
     *   - table
     */
    components: {}
  }, definition)
}

export {
  getDefaultType,
  getType,
  getTypeOrDefault,
  getTypes,
  getCreatableTypes,
  registerType
}
