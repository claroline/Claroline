import {useStore} from 'react-redux'

/**
 * Allows to dynamically mount a reducer in the store.
 * NB. Dynamic reducer can only be mounted at the root of the store.
 *
 * @param {string} key     - The key in the store object where the reducer will be mounted
 * @param {object} reducer - The reducer to mount
 */
function useReducer(key, reducer) {
  const store = useStore()

  store.injectReducer(key, reducer)

  return store
}

export {
  useReducer
}
