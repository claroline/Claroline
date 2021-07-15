import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.sendAnnounce = (aggregateId, announce) => ({
  [API_REQUEST]: {
    type: 'send',
    url: ['claro_announcement_send', {aggregateId: aggregateId, id: announce.id}],
    request: {
      method: 'POST',
      body: JSON.stringify({
        ids: announce.roles
      })
    }
  }
})
