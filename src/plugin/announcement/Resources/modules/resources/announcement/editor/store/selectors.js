import {createSelector} from 'reselect'
import {selectors as announcementSelectors} from '#/plugin/announcement/resources/announcement/store/selectors'

const STORE_NAME = 'announcementEditor'
const FORM_NAME = announcementSelectors.STORE_NAME + '.' + STORE_NAME

const store = createSelector(
  [announcementSelectors.announcement],
  (baseStore) => baseStore[STORE_NAME]
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  store
}
