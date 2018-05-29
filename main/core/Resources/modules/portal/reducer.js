import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = {
  portal: makeListReducer('portal', {}, {}, {
    selectable: false
  })
}

export {
  reducer
}
