import {createSelector} from 'reselect'

const image = state => state.image

const url = createSelector(
  [image],
  (image) => image.url
)

const hashName = createSelector(
  [image],
  (image) => image.hashName
)

export const select = {
  image,
  url,
  hashName
}
