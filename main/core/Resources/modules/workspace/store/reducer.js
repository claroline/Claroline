import {makeReducer, combineReducers} from '#/main/app/store/reducer'

export const reducer = combineReducers({
  accessErrors: combineReducers({
    dismissed: makeReducer(false, {

    }),
    details: makeReducer({}, {

    })
  }),

  tools: makeReducer([], {

  })
})
