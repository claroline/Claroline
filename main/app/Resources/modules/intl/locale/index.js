/**
 *
 * @deprecated use `locale()` instead.
 */
function getLocale() {
  return locale()
}

function locale() {
  const current = document.querySelector('#homeLocale') // todo use platform locale

  if (current) {
    return current.innerHTML.trim()
  }

  return 'en'
}

export {
  getLocale,
  locale
}
