import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store'
import {selectors} from '#/integration/peertube/resources/video/store/selectors'

const reducer = combineReducers({
  form: makeFormReducer(selectors.FORM_NAME, {}, {
    data: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, 'peertube_video')]: (state, action) => action.resourceData.video
    }),
    initialData: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, 'peertube_video')]: (state, action) => action.resourceData.video
    })
  }),
  video: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'peertube_video')]: (state, action) => action.resourceData.video,
    // replaces file data after success updates
    [FORM_SUBMIT_SUCCESS+'/'+selectors.FORM_NAME]: (state, action) => action.updatedData
  })
})

export {
  reducer
}
