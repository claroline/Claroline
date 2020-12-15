import {getApps} from '#/main/app/plugins'

function getSections() {
  // get all sections declared for account
  const sections = getApps('account')

  return Promise.all(
    // boot actions applications
    Object.keys(sections).map(action => sections[action]())
  ).then(loadedSections => loadedSections
    .map(sectionModule => sectionModule.default)
  )
}

export {
  getSections
}
