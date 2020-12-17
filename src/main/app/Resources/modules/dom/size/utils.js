/* global window */

import {constants} from '#/main/app/dom/size/constants'

/**
 * @return {string}
 */
function getWindowSize() {
  let newSize
  if (window.innerWidth < constants.SCREEN_XS_MAX) {
    // XS screen detected
    newSize = constants.SIZE_XS
  } else if (window.innerWidth < constants.SCREEN_SM_MAX) {
    // SM screen detected
    newSize = constants.SIZE_SM
  } else if (window.innerWidth < constants.SCREEN_MD_MAX) {
    // MD screen detected
    newSize = constants.SIZE_MD
  } else {
    // LG screen detected
    newSize = constants.SIZE_LG
  }

  return newSize
}

export {
  getWindowSize
}
