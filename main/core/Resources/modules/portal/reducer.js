import {makeListReducer} from '#/main/app/content/list/store'

const reducer = {
  portal: makeListReducer('portal', {}, {}, {
    selectable: false
  })
}

export {
  reducer
}
