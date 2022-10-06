import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'
import {now} from '#/main/app/intl/date'
import {selectors as searchSelectors} from '#/main/app/content/search/store/selectors'
import {selectors as listSelectors} from '#/main/app/content/list/store/selectors'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors as resourceSelect} from '#/main/core/resource/store'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

const STORE_NAME = 'claroline_dropzone'

const user = (state) => securitySelectors.currentUser(state)
const resource = (state) => state[STORE_NAME]

const userEvaluation = (state) => resourceSelect.resourceEvaluation(state)

const dropzone = createSelector(
  [resource],
  (resource) => resource.dropzone
)

const teams = createSelector(
  [resource],
  (resource) => resource.teams
)

const errorMessage = createSelector(
  [resource],
  (resource) => resource.errorMessage
)

const myDrop = createSelector(
  [resource],
  (resource) => resource.myDrop
)

const drops = createSelector(
  [resource],
  (resource) => resource.drops
)

const currentDrop = createSelector(
  [resource],
  (resource) => resource.currentDrop
)

const correctorDrop = createSelector(
  [resource],
  (resource) => resource.correctorDrop
)

const corrections = createSelector(
  [resource],
  (resource) => resource.corrections
)

const correctionForm = createSelector(
  [resource],
  (resource) => resource.correctionForm
)

const nbCorrections = createSelector(
  [resource],
  (resource) => resource.nbCorrections
)

const myDrops = createSelector(
  [resource],
  (resource) => resource.myDrops
)

const peerDrop = createSelector(
  [resource],
  (resource) => resource.peerDrop
)

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
    return dropzone.planning.drop && now(false) >= dropzone.planning.drop[0] && now(false) <= dropzone.planning.drop[1]
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
  (dropzone) => dropzone.planning.review && now(false) >= dropzone.planning.review[0] && now(false) <= dropzone.planning.review[1]
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
      const currentDate = now(false)
      if (currentDate >= dropzone.planning.drop[0]) {
        if (constants.REVIEW_TYPE_MANAGER === dropzone.parameters.reviewType) {
          //manager review mode
          //finished if end deposit date is reached
          if (currentDate > dropzone.planning.drop[1]) {
            currentState = constants.STATE_FINISHED
          } else if (currentDate <= dropzone.planning.drop[1]){
            currentState = constants.STATE_ALLOW_DROP
          }
        } else if (constants.REVIEW_TYPE_PEER === dropzone.parameters.reviewType){
          //peer review mode
          //finished if end deposit date and review end date are reached
          if (currentDate > dropzone.planning.drop[1] ) {
            if (currentDate < dropzone.planning.review[0]) {
              currentState = constants.STATE_WAITING_FOR_PEER_REVIEW
            } else if (currentDate >= dropzone.planning.review[0] && currentDate <= dropzone.planning.review[1]) {
              currentState = constants.STATE_PEER_REVIEW
            } else {
              currentState = constants.STATE_FINISHED
            }
          } else {
            currentState = constants.STATE_ALLOW_DROP
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

      default:
        if (constants.PLANNING_TYPE_MANUAL === dropzone.planning.type && !isDropEnabledManual) {
          messages.push(trans('drop_disabled_not_active', {}, 'dropzone'))
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

        default:
          if (constants.PLANNING_TYPE_MANUAL === dropzone.planning.type && !isPeerReviewEnabledManual) {
            messages.push(trans('review_disabled_not_active', {}, 'dropzone'))
          }

          break
      }
    }

    return messages
  }
)

const getMyDropStatus = createSelector(
  [myDrop],
  (myDrop) => {
    if(myDrop){
      return myDrop.finished ? constants.DROP_COMPLETED : constants.DROP_IN_PROGRESS
    }
    return constants.DROP_NOT_ATTEMPTED
  }
)

const revision = createSelector(
  [resource],
  (resource) => resource.revision
)

const currentRevisionId = createSelector(
  [resource],
  (resource) => resource.currentRevisionId
)

const slideshowQueryString = (state, name) => {
  const queryParams = []

  const listState = listSelectors.list(state, name)

  // adds list filters
  const currentFilters = searchSelectors.queryString(
    listSelectors.filters(listState)
  )
  if (0 < currentFilters.length) {
    queryParams.push(currentFilters)
  }

  // adds sort by
  const currentSort = listSelectors.sortByQueryString(listState)
  if (0 < currentSort.length) {
    queryParams.push(currentSort)
  }

  if (0 !== queryParams.length) {
    return  '?' + queryParams.join('&')
  }

  return ''
}

export const selectors = {
  STORE_NAME,
  resource,
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
  teams,
  myTeamId,
  errorMessage,
  dropDisabledMessages,
  peerReviewDisabledMessages,
  getMyDropStatus,
  revision,
  currentRevisionId,
  slideshowQueryString
}
