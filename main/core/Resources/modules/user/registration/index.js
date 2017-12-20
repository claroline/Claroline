import {bootstrap} from '#/main/core/utilities/app/bootstrap'

// reducers
import {reducer} from '#/main/core/user/registration/reducer'

import {UserRegistration} from '#/main/core/user/registration/components/main.jsx'

// mount the react application
bootstrap(
  '.user-registration-container',
  UserRegistration,
  reducer
)
