import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {select as listSelectors} from '#/main/app/content/list/store/selectors'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors, actions} from '#/plugin/team/tools/team/store'
import {Team as TeamComponent} from '#/plugin/team/tools/team/components/team'

const Team = connect(
  (state) => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state),
    team: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME + '.teams.current')),
    canEdit: selectors.canEdit(state),
    allowedTeams: selectors.allowedTeams(state),
    myTeams: selectors.myTeams(state),
    teamTotalUsers: listSelectors.totalResults(listSelectors.list(state, selectors.STORE_NAME + '.teams.current.users'))
  }),
  (dispatch) => ({
    selfRegister(teamId) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('register_to_team', {}, 'team'),
        question: trans('register_to_team_confirm_message', {}, 'team'),
        handleConfirm: () => dispatch(actions.selfRegister(teamId))
      }))
    },
    selfUnregister(teamId) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('unregister_from_team', {}, 'team'),
        question: trans('unregister_from_team_confirm_message', {}, 'team'),
        handleConfirm: () => dispatch(actions.selfUnregister(teamId))
      }))
    }
  })
)(TeamComponent)


export {
  Team
}