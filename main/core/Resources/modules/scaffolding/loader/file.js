import {invariant} from 'invariant'

// only used to async load translation files.
// it should not be used for other use cases
// because it will be removed in future.

function loadFile(file) {
  // get some kind of XMLHttpRequest
  const xhrObj = new XMLHttpRequest()

  // open and send a synchronous request
  xhrObj.open('GET', file, false)
  xhrObj.send('')

  if (xhrObj.status === 200) {
    try{
      eval(xhrObj.responseText)
    } catch (e) {
      invariant(false, e)
    }
  }
}

export {
  loadFile
}
