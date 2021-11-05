import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_SELECTION} from '#/main/app/modals/selection'
import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'

import {Overview as OverviewComponent} from '#/plugin/drop-zone/resources/dropzone/overview/components/overview'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/store/actions'

const Overview = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    user: selectors.user(state),
    dropzone: selectors.dropzone(state),
    myDrop: selectors.myDrop(state),
    dropEnabled: selectors.isDropEnabled(state),
    dropDisabledMessages: selectors.dropDisabledMessages(state),
    peerReviewEnabled: selectors.isPeerReviewEnabled(state),
    peerReviewDisabledMessages: selectors.peerReviewDisabledMessages(state),
    nbCorrections: selectors.nbCorrections(state),
    currentState: selectors.currentState(state),
    userEvaluation: selectors.userEvaluation(state),
    errorMessage: selectors.errorMessage(state),
    teams: selectors.teams(state),
    dropStatus: selectors.getMyDropStatus(state)
  }),
  (dispatch) => ({
    startDrop(dropzoneId, dropType, teams = [], navigate, path) {
      switch (dropType) {
        case constants.DROP_TYPE_USER :
          dispatch(actions.initializeMyDrop(dropzoneId, null, navigate, path))
          break
        case constants.DROP_TYPE_TEAM :
          if (teams.length === 1) {
            dispatch(actions.initializeMyDrop(dropzoneId, teams[0].id, navigate, path))
          } else {
            dispatch(
              modalActions.showModal(MODAL_SELECTION, {
                title: trans('team_selection_title', {}, 'dropzone'),
                items: teams.map(t => ({
                  type: t.id,
                  label: t.name,
                  icon: 'fa fa-users'
                })),
                handleSelect: (type) => dispatch(actions.initializeMyDrop(dropzoneId, type.type, navigate, path))
              })
            )
          }
          break
      }
    }
  })
)(OverviewComponent)

export {
  Overview
}
