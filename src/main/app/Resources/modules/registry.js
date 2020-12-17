import invariant from 'invariant'
import merge from 'lodash/merge'

const registries = {}

const supportedEvents = ['add', 'remove']

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
  const registry = { // declare var to simplify references in implementation
    events: {},
    setDefaults: (entry) => entry,
    validate: () => true,
    entries: {}
  }

  registries[registryName] = registry

  function fireEvent(event, entry) {
    if (registry.events[event]) {
      registry.events[event].map(callback => {
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

      // add default values
      const defaultedEntry = registry.setDefaults(entry)

      registry.validate(entry)

      // register new entry
      registry.entries[entryName] = defaultedEntry

      // dispatch event
      fireEvent('add', defaultedEntry)

      return this
    },

    /**
     * Removes an entry from the registry.
     *
     * @param {string} entryName - the name of the entry to remove
     */
    remove(entryName) {
      if (registry.entries[entryName]) {
        // keep a copy of the deleted empty for event
        const entry = merge({}, registry.entries[entryName])

        delete registry.entries[entryName]

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
      invariant(registry.entries[entryName], log(`Entry "${entryName}" is not registered.`))

      return registry.entries[entryName] || null
    },

    /**
     * Gets all registered entries.
     *
     * @return {object}
     */
    all() {
      return registry.entries
    },

    filter(filterCallback) {
      return Object
        .keys(registry.entries)
        .filter((entryName) => filterCallback(registry.entries[entryName]))
        // recreate object with found entries
        .reduce((foundEntries, entryName) => Object.assign(foundEntries, {
          [entryName]: registry.entries[entryName]
        }, {}))
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

      if (!registry.events[event]) {
        registry.events[event] = []
      }

      registry.events[event].push(callback)

      return this
    },

    /**
     * Unbinds an event from the registry.
     *
     * @param {string}   event
     * @param {function} callback
     */
    off(event, callback) {
      if (registry.events[event]) {
        const pos = registry.events[event].indexOf(callback)
        if (-1 !== pos) {
          registry.events[event].splice(pos, 1)
        }
      }

      return this
    },

    /**
     * Registers a function that will add default values before an entry is added.
     *
     * NB. The callback receives the entry as param and MUST return the defaulted entry.
     *
     * @param {function} setDefaultsCallback
     */
    setDefaults(setDefaultsCallback) {
      registry.setDefaults = setDefaultsCallback

      return this
    },

    /**
     * Registers a function that will validate an entry when it is added.
     *
     * NB. The callback receives the entry as param.
     *
     * @param {function} validationCallback
     */
    validate(validationCallback) {
      registry.validate = validationCallback

      return this
    }
  }
}

export {
  declareRegistry
}
