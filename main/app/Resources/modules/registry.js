import invariant from 'invariant'
import merge from 'lodash/merge'

const events = {}
const registries = {}

const supportedEvents = ['get', 'add', 'remove']

/**
 * Declares a new registry.
 *
 * @param {string} registryName - the name of the registry
 *
 * @return {object} - the new registry
 */
function declareRegistry(registryName) {
  invariant(typeof registryName === 'string', `Registry name must be a string. "${registryName}" provided.`)
  invariant(!registries[registryName],        `Registry ${registryName} is already declared.`)

  // initialize registry
  events[registryName] = {}
  registries[registryName] = {}

  function fireEvent(event, entry) {
    if (events[registryName][event]) {
      events[registryName][event].map(callback => {
        callback(entry)
      })
    }
  }

  function log(message) {
    return `REGISTRY[${registryName}] : ${message}`
  }

  return {
    /**
     * Adds a new entry in the registry.
     *
     * @param {string} entryName
     * @param {*}      entry
     */
    add(entryName, entry) {
      invariant(typeof entryName === 'string', log(`Entry name must be a string. "${entryName}" provided.`))

      // register new entry
      registries[registryName][entryName] = entry

      // dispatch event
      fireEvent('add', entry)

      return this
    },

    /**
     * Removes an entry from the registry.
     *
     * @param {string} entryName - the name of the entry to remove
     */
    remove(entryName) {
      if (registries[registryName][entryName]) {
        const entry = merge({}, registries[registryName][entryName])

        delete registries[registryName][entryName]

        // dispatch event
        fireEvent('remove', entry)
      }

      return this
    },

    /**
     * Gets an entry registered in the registry.
     * It will throw an error if the entry is not registered.
     *
     * @param {string} entryName
     *
     * @return {*} - the entry definition
     */
    get(entryName) {
      invariant(registries[registryName][entryName], log(`Entry "${entryName}" is not registered.`))

      fireEvent('get', registries[registryName][entryName])

      return registries[registryName][entryName] || null
    },

    /**
     * Gets all registered entries.
     *
     * @return {object}
     */
    all() {
      return registries[registryName]
    },

    /**
     * Binds an event to the registry.
     *
     * @param {string}   event
     * @param {function} callback
     */
    on(event, callback) {
      invariant(-1 !== supportedEvents.indexOf(event), log(`Event "${event}" is not supported.`))
      invariant(typeof callback === 'function', log(`Event "${event}" callback must be a function.`))

      if (!events[registryName][event]) {
        events[registryName][event] = []
      }

      events[registryName][event].push(callback)

      return this
    },

    /**
     * Unbinds an event from the registry.
     *
     * @param {string}   event
     * @param {function} callback
     */
    off(event, callback) {
      if (events[registryName][event]) {
        const pos = events[registryName][event].indexOf(callback)
        if (-1 !== pos) {
          events[registryName][event].splice(pos, 1)
        }
      }

      return this
    }
  }
}

export {
  declareRegistry
}
