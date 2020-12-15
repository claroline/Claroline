/* global document */

function scrollTo(elementSelector, options) {
  const scrollToElement = document.querySelector(elementSelector)
  if (scrollToElement) {
    scrollToElement.scrollIntoView(options)
  }
}

export {
  scrollTo
}
