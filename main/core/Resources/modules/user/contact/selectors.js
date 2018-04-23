import {createSelector} from 'reselect'

const options = state => state.options.originalData

const optionsData = createSelector(
  [options],
  (options) => options.data
)

export const select = {
  options,
  optionsData
}