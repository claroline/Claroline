import $ from 'jquery'
export default function observe (selector, callback, containers = [document.body]) {
  window.MutationObserver = window.MutationObserver || window.WebKitMutationObserver

  const initialized = []
  // css class to add to your video tag that do not has to be "videojsized"
  const excludeClass = 'not-video-js'
  // create an observer instance

  const observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
      if (mutation.type == 'childList') {
        $(mutation.addedNodes).find(selector).each((i, el) => {
          let keepGoing = true
          initialized.forEach(id => {
            // this is required because otherwise videoJs goes into infinite loop for unknown reason
            // as it automatically adds html_5_api at the end of an id wich triggers the observer wich trigger video js
            // to add html_5_api to the id wich trigger the observer... etc.
            if (el.id.indexOf(id) === 0) {
              keepGoing = false
            }
          })
          // check if the element has the exclude class
          const exclude = el.className.indexOf(excludeClass) > -1

          if (keepGoing && !exclude) {
            callback(el)
            initialized.push(el.id)
          }
        })
      }
    })
  })

  var config = { attributes: false, childList: true, characterData: false, subtree: true }
  containers.forEach(container => observer.observe(container, config))
}
