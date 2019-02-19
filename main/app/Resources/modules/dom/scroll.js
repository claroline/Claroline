/* global document */

function scrollTo(elementSelector) {
  // TODO : find a way to enable smooth scrolling (it's buggy when used in walkthrough)
  const scrollToElement = document.querySelector(elementSelector)
  if (scrollToElement) {
    scrollToElement.scrollIntoView({/*behavior: 'smooth'*/})
  }
}

export {
  scrollTo
}
