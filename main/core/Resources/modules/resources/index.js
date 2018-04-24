import {coreConfiguration} from '#/main/core/plugin'

function getResource(name) {
  if (!coreConfiguration.resources[name]) {
    throw new Error(`You have requested a non existent resource named ${name}`)
  }

  return coreConfiguration.resources[name]
}

export {
  getResource
}
