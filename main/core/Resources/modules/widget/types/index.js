import {coreConfiguration} from '#/main/core/plugin'

function getWidget(name) {
  if (!coreConfiguration.widgets[name]) {
    throw new Error(`You have requested a non existent widget named ${name}`)
  }

  return coreConfiguration.widgets[name]
}

export {
  getWidget
}
