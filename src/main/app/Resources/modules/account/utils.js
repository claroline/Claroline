import isNumber from 'lodash/isNumber'

import {getApps} from '#/main/app/plugins'

function getSections() {
  // get all sections declared for account
  const sections = getApps('account')

  return Promise.all(
    // boot actions applications
    Object.keys(sections).map(action => sections[action]())
  ).then(loadedSections => loadedSections
    .map(sectionModule => sectionModule.default)
    .sort((a, b) => {
      if (isNumber(a.order) && !isNumber(b.order)) {
        return -1
      } else if (!isNumber(a.order) && isNumber(b.order)) {
        return 1
      } else if (isNumber(a.order) && isNumber(b.order)) {
        return a.order - b.order
      } else if (a.label > b.label) {
        return 1
      }

      return 0
    })
  )
}

export {
  getSections
}
