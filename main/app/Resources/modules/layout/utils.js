import {param} from '#/main/app/config'

function showBreadcrumb() {
  return param('display.breadcrumb')
}

export {
  showBreadcrumb
}
