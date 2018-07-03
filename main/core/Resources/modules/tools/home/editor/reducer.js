import {makeFormReducer} from '#/main/core/data/form/reducer'

const reducer = makeFormReducer('editor', {
  data: {
    tabs: [],
    widgets: []
  }
})

export {
  reducer
}
