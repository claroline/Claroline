import {createSelector} from 'reselect'

const page = state => state.page

const fullscreen = createSelector(
  [page],
  (page) => page.fullscreen
)

export const select = {
  page,
  fullscreen
}
