import {API_REQUEST} from '#/main/app/api'

const actions = {}

actions.download = (assertion) => ({
  [API_REQUEST]: {
    url: ['apiv2_assertion_pdf_download', {assertion: assertion.id}],
    request: {
      method: 'GET'
    }
  }
})

export {
  actions
}
