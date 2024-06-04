/* global document */

function scrollTo(elementSelector, options) {
  const scrollToElement = document.querySelector(elementSelector)
  if (scrollToElement) {
    scrollToElement.scrollIntoView(Object.assign({behavior: 'smooth'}, options))
  }
}

export {
  scrollTo
}
