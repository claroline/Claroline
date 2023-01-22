import {getApps} from '#/main/app/plugins'

function getTabs(context, toolPermissions) {
  // get all declared tab types
  const tabs = getApps('evaluation')

  return Promise.all(
    // boot tabs applications
    Object.keys(tabs).map(tab => tabs[tab]())
  ).then((loadedTabs) => loadedTabs
    .map(tabModule => tabModule.default)
    .filter(tab => undefined === tab.displayed || tab.displayed(context, toolPermissions))
  )
}

export {
  getTabs
}
