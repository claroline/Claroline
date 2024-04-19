/* global window */

import {constants} from '#/main/app/dom/size/constants'

/**
 * @return {string}
 */
function getWindowSize() {
  return Object.keys(constants.BREAKPOINTS).reverse().find(sizeString => {
    if (window.innerWidth > constants.BREAKPOINTS[sizeString]) {
      return sizeString
    }
  })
}

export {
  getWindowSize
}
