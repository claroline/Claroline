
export default function observe(selector, callback, containers = [document.body]) {
  window.MutationObserver = window.MutationObserver || window.WebKitMutationObserver

  const initialized = []
  // css class to add to your video tag that do not have to be "videojsized"
  const excludeClass = 'not-video-js'
  // create an observer instance

  const observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
      if (mutation.type === 'childList' && 0 !== mutation.addedNodes.length) {
        mutation.addedNodes.forEach(node => {
          let videos = []
          if (node.tagName === selector.toUpperCase()) {
            videos.push(node)
          } else if (node.querySelectorAll) {
            videos = videos.concat(Array.from(node.querySelectorAll(selector)) || [])
          }

          videos.map(video => {
            let keepGoing = true
            initialized.forEach(id => {
              // this is required because otherwise videoJs goes into infinite loop for unknown reason
              // as it automatically adds html_5_api at the end of an id which triggers the observer which trigger video js
              // to add html_5_api to the id which trigger the observer... etc.
              if (video.id.indexOf(id) === 0) {
                keepGoing = false
              }
            })

            const exclude = video.className.indexOf(excludeClass) > -1
            if (keepGoing && !exclude) {
              callback(video)
              initialized.push(video.id)
            }
          })
        })
      }
    })
  })

  const config = { attributes: false, childList: true, characterData: false, subtree: true }
  containers.forEach(container => observer.observe(container, config))
}
