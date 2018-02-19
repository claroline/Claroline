
import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

import {url} from '#/main/core/api/router'
import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

export const reducer = makePageReducer({}, {
  termOfService: (state = null) => state,
  facets: (state = []) => state,
  options: makeReducer({}, {
    /**
     * Redirects user after successful registration.
     * (It seems a little bit hacky to do it here but it's the simplest way to handle it).
     *
     * @param state
     */
    [FORM_SUBMIT_SUCCESS+'/user']: (state) => {
      if (state.redirectAfterLoginUrl) {
        window.location = state.redirectAfterLoginUrl
      } else {
        switch (state.redirectAfterLoginOption) {
          case 'DESKTOP':
            window.location = url(['claro_desktop_open'])
        }
      }
    }
  }),
  user: makeFormReducer('user', {
    new: true
  })
})
