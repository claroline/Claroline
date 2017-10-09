import {Translator} from './translator'

// makes the translator available in the browser for retro-compatibility purposes
window.Translator = Translator

// exposes our translator instance for direct use in browser
// it will make Translator available through `window.Translator`
/*
(function (root, factory) {
  console.log(root)
  root.Translator = factory()
}(this, function() {
  "use strict";

  return Translator
}))

*/