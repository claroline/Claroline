
export function getLocale() {
  const locale = document.querySelector('#homeLocale')

  if (locale) {
    return locale.innerHTML.trim()
  }

  return 'en'
}