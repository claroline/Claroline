import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {tex} from '#/main/core/translation'
import {generateUrl} from '#/main/core/fos-js-router'
import {Resource as ResourceContainer} from '#/main/core/layout/resource/containers/resource.jsx'
import {viewComponents} from './../views'
import {select as resourceSelect} from '#/main/core/layout/resource/selectors'
import select from './../selectors'
import {actions as editorActions} from './../editor/actions'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {actions as quizActions} from './../actions'

import {VIEW_EDITOR} from './../enums'

let Quiz = props =>
  <ResourceContainer
    edit="#editor"
    editMode={VIEW_EDITOR === props.viewMode}
    save={{
      disabled: !props.saveEnabled,
      action: props.saveQuiz
    }}
    customActions={customActions(props)}
  >
    {React.createElement(viewComponents[props.viewMode], props)}
  </ResourceContainer>

Quiz.propTypes = {
  quiz: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  }).isRequired,
  steps: T.object.isRequired,
  editable: T.bool.isRequired,
  hasUserPapers: T.bool.isRequired,
  registeredUser: T.bool.isRequired,
  viewMode: T.string.isRequired,
  updateViewMode: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  saveQuiz: T.func.isRequired
}

function customActions(props) {
  const actions = []

  // Overview
  actions.push({
    icon: 'fa fa-fw fa-home',
    label: tex('pass_quiz'),
    action: '#overview'
  })

  // Test
  if (props.editable) {
    actions.push({
      icon: 'fa fa-fw fa-play',
      label: tex('exercise_try'),
      action: '#test'
    })
  }

  // Results
  if (props.registeredUser) {
    actions.push({
      icon: 'fa fa-fw fa-list',
      label: tex('results_list'),
      disabled: !props.hasPapers,
      action: '#papers'
    })
  }

  if (props.editable || props.papersAdmin) {
    // Export results
    actions.push({
      icon: 'fa fa-fw fa-table',
      label: tex('export_csv_results'),
      disabled: !props.hasPapers,
      action: generateUrl('exercise_papers_export', {exerciseId: props.quiz.id})
    })
  }

  // Docimology
  if (props.editable || props.docimologyAdmin) {
    actions.push({
      icon: 'fa fa-fw fa-pie-chart',
      label: tex('docimology'),
      action: generateUrl('ujm_exercise_docimology', {id: props.quiz.id})
    })
  }

  // Manual correction
  actions.push({
    icon: 'fa fa-fw fa-check-square-o',
    label: tex('manual_correction'),
    disabled: !props.hasPapers,
    action: '#correction/questions'
  })

  return actions
}

function mapStateToProps(state) {
  return {
    alerts: select.alerts(state),
    quiz: select.quiz(state),
    steps: select.steps(state),
    viewMode: select.viewMode(state),
    editable: resourceSelect.editable(state),
    empty: select.empty(state),
    published: resourceSelect.published(state),
    hasPapers: select.hasPapers(state),
    hasUserPapers: select.hasUserPapers(state),
    papersAdmin: select.papersAdmin(state),
    docimologyAdmin: select.docimologyAdmin(state),
    registeredUser: select.registered(state),
    saveEnabled: select.saveEnabled(state),
    currentQuestionId: state.correction.currentQuestionId
  }
}

function mapDispatchToProps(dispatch) {
  return {
    updateViewMode: mode => dispatch(quizActions.updateViewMode(mode)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props)),
    fadeModal: () => dispatch(modalActions.fadeModal()),
    saveQuiz: () => dispatch(editorActions.save())
  }
}

Quiz = connect(mapStateToProps, mapDispatchToProps)(Quiz)

export {Quiz}
