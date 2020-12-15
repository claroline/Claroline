import get from 'lodash/get'

import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {currentUser} from '#/main/app/security'

import {selectors} from '#/plugin/message/tools/messaging/modals/parameters/store/selectors'

const authenticatedUser = currentUser()

const reducer = makeFormReducer(selectors.STORE_NAME, {
  data: {
    mailNotified: get(authenticatedUser, 'meta.mailNotified')
  }
})

export {
  reducer
}
