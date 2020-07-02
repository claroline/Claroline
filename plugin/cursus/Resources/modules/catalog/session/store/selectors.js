const session = state => state.session
const sessionUser = state => state.sessionUser
const sessionQueue = state => state.sessionQueue
const isFull = state => state.isFull

export const selectors = {
  session,
  sessionUser,
  sessionQueue,
  isFull
}