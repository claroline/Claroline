import {createSelector} from 'reselect'

import {trans} from '#/main/core/translation'
import {now} from '#/main/core/scaffolding/date'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

const dropzone = state => state.dropzone
const user = state => state.user
const teams = state => state.teams
const userEvaluation = state => state.userEvaluation
const errorMessage = state => state.errorMessage
const myDrop = state => state.myDrop

const drops = state => state.drops
const currentDrop = state => state.currentDrop
const correctorDrop = state => state.correctorDrop
const corrections = state => state.corrections
const correctionForm = state => state.correctionForm
const nbCorrections = state => state.nbCorrections
const tools = state => state.tools.data
const myDrops = state => state.myDrops
const peerDrop = state => state.peerDrop

const userHasTeam = createSelector(
  [teams],
  (teams) => teams && 0 < teams.length
)

const dropzoneRequireTeam = createSelector(
  [dropzone],
  (dropzone) => constants.DROP_TYPE_TEAM === dropzone.parameters.dropType
)

const myDropId = createSelector(
  [myDrop],
  (myDrop) => myDrop && myDrop.id ? myDrop.id : null
)

const myTeamId = createSelector(
  [myDrop],
  (myDrop) => myDrop && myDrop.teamId ? myDrop.teamId : null
)

const isDropEnabledManual = createSelector(
  [dropzone],
  (dropzone) => [
    constants.STATE_ALLOW_DROP,
    constants.STATE_ALLOW_DROP_AND_PEER_REVIEW
  ].indexOf(dropzone.planning.state) > -1
)

const isDropEnabledAuto = createSelector(
  [dropzone],
  (dropzone) => {
    return dropzone.planning.drop && now() >= dropzone.planning.drop[0] && now() <= dropzone.planning.drop[1]
  }
)

const isDropEnabled = createSelector(
  [user, errorMessage, dropzone, isDropEnabledManual, isDropEnabledAuto, userHasTeam, dropzoneRequireTeam],
  (user, errorMessage, dropzone, isDropEnabledManual, isDropEnabledAuto, userHasTeam, dropzoneRequireTeam) => {
    return !!user
      && !errorMessage
      && (!dropzoneRequireTeam || userHasTeam)
      && (constants.PLANNING_TYPE_MANUAL === dropzone.planning.type ? isDropEnabledManual : isDropEnabledAuto)
  }
)

const isPeerReviewEnabledManual = createSelector(
  [dropzone],
  (dropzone) => [
    constants.STATE_PEER_REVIEW,
    constants.STATE_ALLOW_DROP_AND_PEER_REVIEW
  ].indexOf(dropzone.planning.state) > -1
)

const isPeerReviewEnabledAuto = createSelector(
  [dropzone],
  (dropzone) => dropzone.planning.review && now() >= dropzone.planning.review[0] && now() <= dropzone.planning.review[1]
)

const isPeerReviewEnabled = createSelector(
  [user, dropzone, isPeerReviewEnabledManual, isPeerReviewEnabledAuto, myDrop, nbCorrections],
  (user, dropzone, isPeerReviewEnabledManual, isPeerReviewEnabledAuto, myDrop, nbCorrections) => {
    return !!user
      && constants.REVIEW_TYPE_PEER === dropzone.parameters.reviewType
      && (!!myDrop && myDrop.finished)
      && (constants.PLANNING_TYPE_MANUAL === dropzone.planning.type ? isPeerReviewEnabledManual : isPeerReviewEnabledAuto)
      && nbCorrections < dropzone.parameters.expectedCorrectionTotal
  }
)

const currentState = createSelector(
  [dropzone],
  (dropzone) => {
    let currentState = constants.STATE_NOT_STARTED

    if (constants.PLANNING_TYPE_MANUAL === dropzone.planning.type) {
      // manual planning, just get the state defined by managers
      currentState = dropzone.planning.state
    } else {
      // auto planning, calculate state from date ranges
      const currentDate = now()

      if (currentDate >= dropzone.planning.drop[0]) {
        if (currentDate > dropzone.planning.drop[1] && currentDate > dropzone.planning.review[1]) {
          currentState = constants.STATE_FINISHED
        } else if (currentDate > dropzone.planning.drop[1] && currentDate < dropzone.planning.review[0]) {
          currentState = constants.STATE_WAITING_FOR_PEER_REVIEW
        } else {
          if (dropzone.planning.drop[0] <= currentDate && currentDate <= dropzone.planning.drop[1]) {
            currentState = constants.STATE_ALLOW_DROP
          }
          if (dropzone.planning.review[0] <= currentDate && currentDate <= dropzone.planning.review[1]) {
            currentState = constants.STATE_PEER_REVIEW
          }
        }
      }
    }

    return currentState
  }
)

// get why drop is disabled
const dropDisabledMessages = createSelector(
  [user, errorMessage, dropzone, currentState, dropzoneRequireTeam, userHasTeam, isDropEnabledManual],
  (user, errorMessage, dropzone, currentState, dropzoneRequireTeam, userHasTeam, isDropEnabledManual) => {
    const messages = []

    if (errorMessage) {
      messages.push(errorMessage)
    }

    // anonymous user error
    if (!user) {
      messages.push(trans('drop_disabled_user_required', {}, 'dropzone'))
    }

    // no team error
    if (dropzoneRequireTeam && !userHasTeam) {
      messages.push(trans('drop_disabled_team_required', {}, 'dropzone'))
    }

    // state error
    switch (currentState) {
      // not started error
      case constants.STATE_NOT_STARTED:
        messages.push(trans('drop_disabled_not_started', {}, 'dropzone'))
        break

      // finished error
      case constants.STATE_FINISHED:
        messages.push(trans('drop_disabled_finished', {}, 'dropzone'))
        break

      // otherwise checks drop date boundaries
      default:
        if (constants.PLANNING_TYPE_MANUAL === dropzone.planning.type) {
          if (!isDropEnabledManual) {
            messages.push(trans('drop_disabled_not_active', {}, 'dropzone'))
          }
        } else {
          if (now() < dropzone.planning.drop[0]) {
            // drop has not already started
            messages.push(trans('drop_disabled_not_started', {}, 'dropzone'))
          } else if (now() > dropzone.planning.drop[1]) {
            // drop has already finished
            messages.push(trans('drop_disabled_finished', {}, 'dropzone'))
          }
        }

        break
    }

    return messages
  }
)

const peerReviewDisabledMessages = createSelector(
  [user, dropzone, isPeerReviewEnabledManual, myDrop, nbCorrections],
  (user, dropzone, isPeerReviewEnabledManual, myDrop, nbCorrections) => {
    const messages = []

    // if peer review is disabled, just skip errors
    if (constants.REVIEW_TYPE_PEER === dropzone.parameters.reviewType) {
      // anonymous user error
      if (!user) {
        messages.push(trans('review_disabled_user_required', {}, 'dropzone'))
      }

      if (!myDrop || !myDrop.finished) {
        messages.push(trans('review_disabled_drop_not_finished', {}, 'dropzone'))
      }

      if (nbCorrections === dropzone.parameters.expectedCorrectionTotal) {
        messages.push(trans('review_disabled_corrections_done', {}, 'dropzone'))
      }

      // state error
      switch (currentState) {
        // not started error
        case constants.STATE_NOT_STARTED:
          messages.push(trans('review_disabled_not_started', {}, 'dropzone'))
          break

        // finished error
        case constants.STATE_FINISHED:
          messages.push(trans('review_disabled_finished', {}, 'dropzone'))
          break

        // otherwise checks drop date boundaries
        default:
          if (constants.PLANNING_TYPE_MANUAL === dropzone.planning.type) {
            if (!isPeerReviewEnabledManual) {
              messages.push(trans('review_disabled_not_active', {}, 'dropzone'))
            }
          } else {
            if (now() < dropzone.planning.review[0]) {
              // drop has not already started
              messages.push(trans('review_disabled_not_started', {}, 'dropzone'))
            } else if (now() > dropzone.planning.review[1]) {
              // drop has already finished
              messages.push(trans('review_disabled_finished', {}, 'dropzone'))
            }
          }

          break
      }
    }

    return messages
  }
)

export const select = {
  user,
  userEvaluation,
  dropzone,
  myDrop,
  myDrops,
  myDropId,
  peerDrop,
  isDropEnabled,
  isPeerReviewEnabled,
  currentState,
  drops,
  currentDrop,
  correctorDrop,
  corrections,
  correctionForm,
  nbCorrections,
  tools,
  teams,
  myTeamId,
  errorMessage,
  dropDisabledMessages,
  peerReviewDisabledMessages
}
