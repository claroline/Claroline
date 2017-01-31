import uuid from 'uuid'
import {generateUrl} from '#/main/core/fos-js-router'

const AUTH_WINDOW = 'claro_auth_window'

export const ERROR_AUTH_WINDOW_CLOSED = 'Authentication window closed unexpectedly'
export const ERROR_AUTH_WINDOW_BLOCKED = 'Authentication window blocked by browser'

/**
 * Handles (re-)authentication in a separate window/tab. Returns a promise which
 * will be resolved on authentication success and rejected if the authentication
 * window is blocked or closed before actual authentication took place.
 */
export function authenticate() {
  return new Promise((resolve, reject) => {
    // generate a hash id for the authentication attempt (it will serve to catch the
    // authentication event, avoiding a generic "authenticated" event; see below)
    const authHash = uuid()
    const authUrl = generateUrl('trigger_auth', {hash: authHash}, true)

    // open a dedicated (named) window for (re-)authentication
    const authWindow = window.open(authUrl, AUTH_WINDOW)

    if (!authWindow) {
      // reference will be null if a popup blocker is active
      return reject(new Error(ERROR_AUTH_WINDOW_BLOCKED))
    }

    // the following should re-focus any pre-existing auth window (doesn't work great though...)
    authWindow.focus()

    let authenticated = false

    // we must detect if the auth window was closed before authentication,
    // but as no event is provided for that, we have to setup a timer and check
    // regularly what's the window state
    const closeCheck = setInterval(() => {
      if (authWindow.closed && !authenticated) {
        window.clearInterval(closeCheck)
        reject(new Error(ERROR_AUTH_WINDOW_CLOSED))
      }
    }, 100)

    // if the authentication succeeded, the auth window will dispatch a custom
    // event named after the hash id
    window.addEventListener(authHash, () => {
      authenticated = true
      authWindow.close()
      window.clearInterval(closeCheck)
      resolve()
    })
  })
}
