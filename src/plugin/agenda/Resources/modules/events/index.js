import {getApp, getApps} from '#/main/app/plugins'

const REGISTRY = 'events'

function getEvent(name) {
  return getApp(REGISTRY, name)()
}

function getEvents() {
  // get all event types
  const eventTypes = getApps(REGISTRY)

  return Promise.all(
    // boot actions applications
    Object.keys(eventTypes).map(eventType => eventTypes[eventType]())
  )
}

export {
  getEvent,
  getEvents
}
