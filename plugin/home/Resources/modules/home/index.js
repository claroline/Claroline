import {getApp, getApps} from '#/main/app/plugins'

function getTabs(context = null) {
  // get all declared tab types
  const tabs = getApps('home')

  return Promise.all(
    // boot tabs applications
    Object.keys(tabs).map(tab => tabs[tab]())
  ).then((loadedTabs) => loadedTabs
    .map(tabModule => tabModule.default)
    .filter(tab => !context || !tab.context || -1 !== tab.context.findIndex(c => c === context))
  )
}

function getTab(name) {
  return getApp('home', name)()
    .then(tabModule => tabModule.default)
}

export {
  getTabs,
  getTab
}
