import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors, actions} from '#/plugin/team/tools/team/store'
import {Teams as TeamsComponent} from '#/plugin/team/tools/team/components/teams'

const Teams = connect(
  (state) => ({
    path: toolSelectors.path(state),
    workspaceId: toolSelectors.contextData(state).uuid,
    myTeams: selectors.myTeams(state),
    canEdit: selectors.canEdit(state)
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
    },
    fillTeams(teams) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('fill_teams', {}, 'team'),
        question: trans('fill_selected_teams_confirm_message', {}, 'team'),
        handleConfirm: () => dispatch(actions.fillTeams(teams))
      }))
    },
    emptyTeams(teams) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('empty_teams', {}, 'team'),
        question: trans('empty_selected_teams_confirm_message', {}, 'team'),
        handleConfirm: () => dispatch(actions.emptyTeams(teams))
      }))
    }
  })
)(TeamsComponent)

export {
  Teams
}
