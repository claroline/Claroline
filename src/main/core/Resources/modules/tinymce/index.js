/* global window */

import {tinymce as originalTinymce} from 'tinymce/tinymce'

/**
 * Get the current TinyMCE instance.
 *
 * @returns {object}
 */
function getTinyMCE() {
  // we reuse the instance from browser, because it contains injected plugins
  return window.tinymce || originalTinymce
}

// reexport tinymce object
const tinymce = getTinyMCE()

export {
  tinymce
}
