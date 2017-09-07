import invariant from 'invariant'

import {BOOLEAN_TYPE,  booleanDefinition}  from '#/main/core/layout/data/types/boolean/index'
import {COLOR_TYPE,    colorDefinition}    from '#/main/core/layout/data/types/color'
import {DATE_TYPE,     dateDefinition}     from '#/main/core/layout/data/types/date/index'
import {DATETIME_TYPE, datetimeDefinition} from '#/main/core/layout/data/types/datetime'
import {FLAG_TYPE,     flagDefinition}     from '#/main/core/layout/data/types/flag/index'
import {HTML_TYPE,     htmlDefinition}     from '#/main/core/layout/data/types/html'
import {IP_TYPE,       ipDefinition}       from '#/main/core/layout/data/types/ip'
import {NUMBER_TYPE,   numberDefinition}   from '#/main/core/layout/data/types/number'
import {STRING_TYPE,   stringDefinition}   from '#/main/core/layout/data/types/string'

// the list of registered data types
export const dataTypes = {}

// register default types
registerType(BOOLEAN_TYPE,  booleanDefinition)
registerType(COLOR_TYPE,    colorDefinition)
registerType(DATE_TYPE,     dateDefinition)
registerType(DATETIME_TYPE, datetimeDefinition)
registerType(FLAG_TYPE,     flagDefinition)
registerType(HTML_TYPE,     htmlDefinition)
registerType(IP_TYPE,       ipDefinition)
registerType(NUMBER_TYPE,   numberDefinition)
registerType(STRING_TYPE,   stringDefinition)

/**
 * Validates & registers a data type.
 *
 * @param {string} typeName
 * @param {object} typeDefinition
 */
export function registerType(typeName, typeDefinition) {
  invariant(typeof typeName === 'string', `Data type name must be a string. "${typeName}" provided.`)
  invariant(!dataTypes[typeName],         `Data type ${typeName} is already registered.`)

  const definition = setDefinitionDefaults(typeDefinition)
  validateDefinition(definition)

  // register the new type
  dataTypes[typeName] = definition
}

export function getType(typeName) {
  invariant(dataTypes[typeName], `Data type "${typeName}" is not registered.`)

  return dataTypes[typeName]
}

export function getDefaultType() {
  return dataTypes[STRING_TYPE]
}

export function getTypeOrDefault(typeName) {
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
    validate: () => true,

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
