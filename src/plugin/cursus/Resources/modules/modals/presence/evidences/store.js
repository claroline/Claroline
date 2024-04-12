import {API_REQUEST} from '#/main/app/api'
export const actions = {}

actions.createFiles = (presence, files, onSuccess) => {
  const formData = new FormData()
  files.forEach((file, index) => formData.append(index, file))

  console.log( files )

  return ({
    [API_REQUEST]: {
      url: ['apiv2_cursus_presence_evidences_upload', {id: presence.id}],
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
