/* global document */

import invariant from 'invariant'

/**
 * Exposes the current logged user.
 *
 * NB. For now it's added in the data set of a DOM tag by Twig.
 */

let user = null
let userLoaded = false // because for anonymous, currentUser stay null

/**
 * Loads configuration object from DOM anchor.
 */
function load() {
  const userEl = document.querySelector('#current-user')

  invariant(userEl, 'Can not find current user.')

  user = JSON.parse(userEl.dataset.user) || null
  userLoaded = true
}


function currentUser() {
  if (!userLoaded) {
    load()
  }

  return user
}

export {
  currentUser
}
