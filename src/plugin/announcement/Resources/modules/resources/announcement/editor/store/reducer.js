import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store'

import {selectors as announcementSelectors} from '#/plugin/announcement/resources/announcement/store/selectors'
import {selectors} from '#/plugin/announcement/resources/announcement/editor/store/selectors'

const reducer = makeFormReducer(selectors.FORM_NAME, {}, {
  data: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, announcementSelectors.STORE_NAME)]: (state, action) => action.resourceData.announcement
  }),
  originalData: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, announcementSelectors.STORE_NAME)]: (state, action) => action.resourceData.announcement
  })
})

export {
  reducer
}
