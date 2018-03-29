import invariant from 'invariant'

function loadFile(file) {
  // get some kind of XMLHttpRequest
  const xhrObj = new XMLHttpRequest()

  // open and send a synchronous request
  xhrObj.open('GET', file, false)
  xhrObj.send('')

  if (xhrObj.status === 200) {
    try {
      eval(xhrObj.responseText)
    } catch(error) {
      invariant(true, error)
    }
  }
}

export {
  loadFile
}
