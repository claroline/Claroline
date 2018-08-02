import get from 'lodash/get'

// NB. for now it reuses the store created by `makeFormReducer`

// retrieves a details instance in the store
const details = (state, detailsName) => get(state, detailsName)
const data = (detailsState) => detailsState.originalData

export const selectors = {
  details,
  data
}
