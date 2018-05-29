import createHistory from 'history/createHashHistory'
import createMemoryHistory from 'history/createMemoryHistory'

// todo : remove me. It will no longer work with the multiple routers

/**
 * Creates application navigation history.
 */
const history = createHistory()

const memoryHistory = createMemoryHistory()

export {
  history,
  memoryHistory
}
