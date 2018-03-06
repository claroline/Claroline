/**
 *
 * @deprecated use `locale()` instead.
 */
function getLocale() {
  return locale()
}

function locale() {
  const current = document.querySelector('#homeLocale')

  if (current) {
    return current.innerHTML.trim()
  }

  return 'en'
}

export {
  getLocale,
  locale
}