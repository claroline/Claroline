import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_SELECTION} from '#/main/app/modals/selection'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ResourceOverview} from '#/main/core/resource/components/overview'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/actions'
import {DropzoneType, DropType} from '#/plugin/drop-zone/resources/dropzone/prop-types'

import {Parameters} from '#/plugin/drop-zone/resources/dropzone/overview/components/parameters'
import {Timeline} from '#/plugin/drop-zone/resources/dropzone/overview/components/timeline'

const OverviewComponent = props =>
  <ResourceOverview
    contentText={props.dropzone.instruction}
    evaluation={props.userEvaluation}
    display={{
      score: props.dropzone.display.showScore,
      scoreMax: props.dropzone.parameters.scoreMax,
      feedback: props.dropzone.display.showFeedback
    }}
    feedbacks={{
      success: props.dropzone.display.successMessage,
      failure: props.dropzone.display.failMessage
    }}
    statusTexts={constants.DROP_STATUS}
    details={[
      [
        trans('drop_date', {}, 'dropzone'),
        props.myDrop && props.myDrop.dropDate ?
          displayDate(props.myDrop.dropDate, false, true) :
          trans('not_submitted', {}, 'dropzone')
      ],
      constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType && [
        'Nbre de corrections reçues',
        `${props.myDrop ? props.myDrop.corrections.length : '0'} / ${props.dropzone.parameters.expectedCorrectionTotal}`
      ],
      constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType && [
        'Nbre de corrections faîtes',
        `${props.nbCorrections} / ${props.dropzone.parameters.expectedCorrectionTotal}`
      ]
    ].filter(value => !!value)}

    actions={[
      // todo add show Drop
      {
        type: !props.myDrop ? CALLBACK_BUTTON : LINK_BUTTON,
        icon: 'fa fa-fw fa-upload icon-with-text-right',
        label: trans(!props.myDrop ? 'start_evaluation' : (!props.myDrop.finished ? 'continue_evaluation' : 'show_evaluation'), {}, 'dropzone'),
        target: props.myDrop ? `${props.path}/my/drop` : undefined,
        callback: !props.myDrop ?
          () => props.startDrop(props.dropzone.id, props.dropzone.parameters.dropType, props.teams, props.history.push, props.path) :
          undefined,
        primary: !props.myDrop || !props.myDrop.finished,
        disabled: !props.dropEnabled,
        disabledMessages: props.dropDisabledMessages
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-check-square-o icon-with-text-right',
        label: trans('correct_a_copy', {}, 'dropzone'),
        target: `${props.path}/peer/drop`,
        primary: props.myDrop && props.myDrop.finished,
        disabled: !props.peerReviewEnabled,
        disabledMessages: props.peerReviewDisabledMessages
      }
    ]}
  >
    <section className="resource-parameters">
      <h3 className="h2">{trans('evaluation_configuration', {}, 'dropzone')}</h3>

      <Parameters
        dropType={props.dropzone.parameters.dropType}
        reviewType={props.dropzone.parameters.reviewType}
      />

      <Timeline
        state={props.currentState}
        planning={props.dropzone.planning}
        reviewType={props.dropzone.parameters.reviewType}
      />
    </section>
  </ResourceOverview>

OverviewComponent.propTypes = {
  path: T.string.isRequired,
  user: T.object,
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  myDrop: T.shape(DropType.propTypes),
  dropEnabled: T.bool.isRequired,
  dropDisabledMessages: T.arrayOf(T.string).isRequired,
  peerReviewEnabled: T.bool.isRequired,
  peerReviewDisabledMessages: T.arrayOf(T.string).isRequired,
  nbCorrections: T.number.isRequired,
  currentState: T.oneOf(
    Object.keys(constants.PLANNING_STATES.all)
  ).isRequired,
  userEvaluation: T.shape({
    status: T.string
  }),
  errorMessage: T.string,
  teams: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  })),
  startDrop: T.func.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  dropStatus: T.string
}

OverviewComponent.defaultProps = {
  myDrop: {}
}

const Overview = withRouter(connect(
  (state) => ({
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
)(OverviewComponent))

export {
  Overview
}
