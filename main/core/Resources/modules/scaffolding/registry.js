import {checkPropTypes} from 'prop-types'
import invariant from 'invariant'
import merge from 'lodash/merge'

const registries = {}

/**
 * Declares a new registry.
 *
 * @param {string} registryName   - the name of the registry
 * @param {object} entryPropTypes - the format of the entries definition
 * @param {object} entryDefaults  - the default entries props
 *
 * @return {object} - the new registry
 */
function registry(registryName, entryPropTypes, entryDefaults) {
  invariant(typeof registryName === 'string', `Registry name must be a string. "${registryName}" provided.`)
  invariant(!registries[registryName],        `Registry ${registryName} is already declared.`)

  // initialize registry
  registries[registryName] = {}

  return {
    /**
     * Adds a new entry in the registry.
     *
     * @param {string} entryName
     * @param {object} entryDefinition
     */
    add(entryName, entryDefinition = {}) {
      // adds entry defaults
      const definition = merge({}, entryDefaults || {}, entryDefinition)

      // validates entry
      invariant(typeof entryName === 'string', `Entry name must be a string. "${entryName}" provided.`)
      checkPropTypes(entryPropTypes, definition, 'prop', `RegistryEntry<${registryName}>`)

      // register new entry
      registries[registryName] = definition
    },

    /**
     * Gets an entry registered in the registry.
     * It will throw an error if the entry is not registered.
     *
     * @param {string} entryName
     *
     * @return {mixed} - the entry definition
     */
    get(entryName) {
      invariant(registries[registryName][entryName], `Entry "${entryName}" is not registered.`)

      return registries[registryName][entryName] || null
    },
    /**
     * Gets all registered entries.
     *
     * @return {object}
     */
    all() {
      return registries[registryName]
    }
  }
}

export {
  registry
}
