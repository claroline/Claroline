import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {displayDate} from '#/main/core/scaffolding/date'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_GENERIC_TYPE_PICKER} from '#/main/core/layout/modal'

import {ResourceOverview} from '#/main/core/resource/components/overview.jsx'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/actions'
import {DropzoneType, DropType} from '#/plugin/drop-zone/resources/dropzone/prop-types'

import {Parameters} from '#/plugin/drop-zone/resources/dropzone/overview/components/parameters.jsx'
import {Timeline} from '#/plugin/drop-zone/resources/dropzone/overview/components/timeline.jsx'

const OverviewComponent = props =>
  <ResourceOverview
    contentText={props.dropzone.instruction ||
      <span className="empty-text">{trans('no_instruction', {}, 'dropzone')}</span>
    }
    progression={{
      status: props.userEvaluation ? props.userEvaluation.status : undefined,
      statusTexts: {
        opened: 'Vous n\'avez jamais soumis de travaux.'
      },
      score: {
        displayed: props.dropzone.display.showScore,
        current: props.myDrop ? props.myDrop.score : null,
        total: props.dropzone.parameters.scoreMax
      },
      feedback: {
        displayed: props.dropzone.display.showFeedback,
        success: props.dropzone.display.successMessage,
        failure: props.dropzone.display.failMessage
      },
      details: [
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
      ].filter(value => !!value)
    }}
    actions={[
      // todo add show Drop
      {
        icon: 'fa fa-fw fa-upload icon-with-text-right',
        label: trans(!props.myDrop ? 'start_evaluation' : (!props.myDrop.finished ? 'continue_evaluation' : 'show_evaluation'), {}, 'dropzone'),
        action: !props.myDrop ? () => props.startDrop(props.dropzone.id, props.dropzone.parameters.dropType, props.teams) : '#/my/drop',
        primary: !props.myDrop || !props.myDrop.finished,
        disabled: !props.dropEnabled,
        disabledMessages: props.dropDisabledMessages
      }, {
        icon: 'fa fa-fw fa-check-square-o icon-with-text-right',
        label: trans('correct_a_copy', {}, 'dropzone'),
        action: '#/peer/drop',
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
    status: T.string.isRequired
  }),
  errorMessage: T.string,
  teams: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired
  })),
  startDrop: T.func.isRequired
}

OverviewComponent.defaultProps = {
  myDrop: {}
}

const Overview = connect(
  (state) => ({
    user: select.user(state),
    dropzone: select.dropzone(state),
    myDrop: select.myDrop(state),
    dropEnabled: select.isDropEnabled(state),
    dropDisabledMessages: select.dropDisabledMessages(state),
    peerReviewEnabled: select.isPeerReviewEnabled(state),
    peerReviewDisabledMessages: select.peerReviewDisabledMessages(state),
    nbCorrections: select.nbCorrections(state),
    currentState: select.currentState(state),
    userEvaluation: select.userEvaluation(state),
    errorMessage: select.errorMessage(state),
    teams: select.teams(state)
  }),
  (dispatch) => ({
    startDrop(dropzoneId, dropType, teams = []) {
      switch (dropType) {
        case constants.DROP_TYPE_USER :
          dispatch(actions.initializeMyDrop(dropzoneId))
          break
        case constants.DROP_TYPE_TEAM :
          if (teams.length === 1) {
            dispatch(actions.initializeMyDrop(dropzoneId, teams[0].id))
          } else {
            dispatch(
              modalActions.showModal(MODAL_GENERIC_TYPE_PICKER, {
                title: trans('team_selection_title', {}, 'dropzone'),
                types: teams.map(t => ({
                  type: t.id,
                  name: t.name,
                  icon: 'fa fa-users'
                })),
                handleSelect: (type) => dispatch(actions.initializeMyDrop(dropzoneId, type.type))
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
