import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {AboutModal as AboutModalComponent} from '#/main/community/team/modals/about/components/modal'
import {actions, reducer, selectors} from '#/main/community/team/modals/about/store'

const AboutModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      team: selectors.team(state)
    }),
    (dispatch) => ({
      get(id) {
        dispatch(actions.get(id))
      },
      reset() {
        dispatch(actions.load(null))
      }
    })
  )(AboutModalComponent)
)

export {
  AboutModal
}
