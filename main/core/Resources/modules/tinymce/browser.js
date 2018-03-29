/* global window, document */

import $ from 'jquery'
import tinymce from 'tinymce/tinymce'

import {config} from '#/main/core/tinymce/config'

// makes tinymce available in the browser for retro-compatibility purposes
window.tinymce = tinymce

// TODO : temp : avoid breaking old stuff
tinymce.claroline = {
  buttons: {},
  plugins: {},
  css: []
}

/** Events (for retro-compatibility) **/
$(document).ready(function () {
  function initialize(element) {
    tinymce.init(
      Object.assign({}, config, {
        target: element
      })
    )

    element.tinyMceReady = true
  }

  function check() {
    // Query for elements matching the specified selector
    const elements = document.querySelectorAll('.claroline-tiny-mce')
    for (let j = 0; j < elements.length; j++) {
      let element = elements[j]
      // Make sure the callback isn't invoked with the
      // same element more than once
      if (!element.tinyMceReady) {
        // Invoke the callback with the element
        initialize(element)
      }
    }
  }

  // Watch for changes in the document
  const observer = new MutationObserver(check)
  observer.observe(document.documentElement, {
    childList: true,
    subtree: true
  })

  // Check if the element is currently in the DOM
  check()
})
