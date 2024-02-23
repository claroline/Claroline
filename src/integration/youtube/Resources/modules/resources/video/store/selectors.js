import {createSelector} from 'reselect'

const STORE_NAME = 'youtube_video'
const FORM_NAME = 'youtube_video.form'

const resource = (state) => state[STORE_NAME]

const video = createSelector(
  [resource],
  (resource) => resource.video
)

const progression = createSelector(
  [resource],
  (resource) => resource.progression
)

const url = createSelector(
  [video],
  (video) => video.url
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  resource,
  video,
  progression,
  url
}
