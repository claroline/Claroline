import {param} from '#/main/app/config'

function locale() {
  return param('locale.current') || 'en'
}

export {
  locale
}
