import {combineReducers} from '#/main/app/store/reducer'

import {reducer as materialReducer} from '#/plugin/booking/tools/booking/material/store/reducer'
import {reducer as roomReducer} from '#/plugin/booking/tools/booking/room/store/reducer'

export const reducer = combineReducers({
  material: materialReducer,
  room: roomReducer
})
