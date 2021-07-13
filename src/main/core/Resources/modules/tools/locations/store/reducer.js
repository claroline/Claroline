import {combineReducers} from '#/main/app/store/reducer'

import {reducer as locationReducer} from '#/main/core/tools/locations/location/store/reducer'
import {reducer as materialReducer} from '#/main/core/tools/locations/material/store/reducer'
import {reducer as roomReducer} from '#/main/core/tools/locations/room/store/reducer'

const reducer = combineReducers({
  locations: locationReducer,
  material: materialReducer,
  room: roomReducer
})

export {
  reducer
}
