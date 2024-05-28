import {createSelector} from 'reselect'

const STORE_NAME = 'youtube_video'
const FORM_NAME = 'youtube_video.form'

const store = (state) => state[STORE_NAME]

const video = createSelector(
  [store],
  (store) => store.resource
)

const progression = createSelector(
  [store],
  (store) => store.progression
)

const url = createSelector(
  [video],
  (video) => video.url
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  video,
  progression,
  url
}
