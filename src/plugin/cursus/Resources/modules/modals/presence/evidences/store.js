import {API_REQUEST} from '#/main/app/api'
export const actions = {}

actions.createFile = (presence, file, onSuccess) => {
  const formData = new FormData()
  formData.append(0, file)

  return ({
    [API_REQUEST]: {
      url: ['apiv2_cursus_presence_evidence_upload', {id: presence.id}],
      type: 'upload',
      request: {
        method: 'POST',
        body: formData,
        headers: new Headers({
          'X-Requested-With': 'XMLHttpRequest'
        })
      },
      success: (response) => {
        if (onSuccess) {
          onSuccess(response)
        }
      }
    }
  })
}
