/* global document */

function scrollTo(elementSelector) {
  const scrollToElement = document.querySelector(elementSelector)
  if (scrollToElement) {
    scrollToElement.scrollIntoView({behavior: 'smooth'})
  }
}

export {
  scrollTo
}
