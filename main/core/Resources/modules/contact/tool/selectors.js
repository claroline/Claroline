import {createSelector} from 'reselect'

const options = state => state.options

const optionsData = createSelector(
  [options],
  (options) => options.data
)

export const select = {
  options,
  optionsData
}