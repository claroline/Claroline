import React, {PropTypes as T} from 'react'
import {connect} from 'react-redux'
import {t, tex} from './../../utils/translate'
import PageHeader from './../../components/layout/page-header.jsx'
import PageActions from './../../components/layout/page-actions.jsx'
import {showModal, fadeModal} from './../../modal/actions'
import {makeModal} from './../../modal'
import {Overview} from './../overview/overview.jsx'
import {Player} from './../player/components/player.jsx'
import {AttemptEnd} from './../player/components/attempt-end.jsx'
import {Editor} from './../editor/components/editor.jsx'
import {Papers} from './../papers/components/papers.jsx'
import {Paper} from './../papers/components/paper.jsx'
import {Questions} from './../correction/components/questions.jsx'
import {Answers} from './../correction/components/answers.jsx'
import select from './../selectors'
import {selectors as correctionSelectors} from './../correction/selectors'
import {actions as editorActions} from './../editor/actions'
import {actions as correctionActions} from './../correction/actions'
import {actions} from './../actions'
import {
  VIEW_OVERVIEW,
  VIEW_PLAYER,
  VIEW_EDITOR,
  VIEW_PAPERS,
  VIEW_PAPER,
  VIEW_CORRECTION_QUESTIONS,
  VIEW_CORRECTION_ANSWERS,
  VIEW_ATTEMPT_END
} from './../enums'

let Quiz = props =>
  <main className="page">
    <PageHeader title={props.quiz.title}>
      {props.editable &&
        <PageActions actions={viewActions(props.viewMode, props)} />
      }
      {!props.editable && props.registeredUser &&
        <PageActions actions={userViewActions(props.viewMode, props)} />
      }
    </PageHeader>
    {props.modal.type &&
      props.createModal(
        props.modal.type,
        props.modal.props,
        props.modal.fading
      )
    }
    <div className="page-content">
      {viewComponent(props.viewMode, props)}
    </div>
  </main>

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
  saveQuiz: T.func.isRequired,
  createModal: T.func.isRequired,
  showModal: T.func.isRequired,
  fadeModal: T.func.isRequired,
  modal: T.shape({
    type: T.string,
    fading: T.bool.isRequired,
    props: T.object.isRequired
  })
}

function userViewActions(view, props) {
  const divider = {
    primary: true,
    divider: true
  }

  const overviewAction = {
    icon: 'fa fa-fw fa-times',
    label: t('close'),
    handleAction: '#overview',
    primary: true
  }

  const papersAction = {
    icon: 'fa fa-fw fa-list',
    label: tex('results_list'),
    disabled: props.canViewPapers ? !props.canViewPapers && !props.hasPapers : !props.hasUserPapers,
    handleAction: '#papers'
  }

  switch (view) {
    case VIEW_PLAYER:
      return [
        overviewAction,
        divider,
        papersAction
      ]
    case VIEW_ATTEMPT_END:
      return [
        overviewAction,
        divider,
        papersAction
      ]
    case VIEW_PAPERS:
      return [
        overviewAction,
        divider,
        papersAction
      ]
    case VIEW_PAPER:
      return [
        overviewAction,
        divider,
        papersAction
      ]
    case VIEW_OVERVIEW:
    default:
      return [
        papersAction
      ]
  }
}

function viewActions(view, props) {
  const divider = {
    primary: true,
    divider: true
  }

  const overviewAction = {
    icon: 'fa fa-fw fa-times',
    label: t('close'),
    handleAction: '#overview',
    primary: true
  }

  const editAction = {
    icon: 'fa fa-fw fa-pencil',
    label: t('edit'),
    handleAction: '#editor',
    primary: true
  }

  const testAction = {
    icon: 'fa fa-fw fa-play',
    label: tex('exercise_try'),
    handleAction: '#test',
    primary: true
  }

  const saveAction = {
    icon: 'fa fa-fw fa-save',
    label: t('save'),
    disabled: !props.saveEnabled,
    handleAction: props.saveQuiz,
    primary: true
  }

  const saveCorrectionAction = {
    icon: 'fa fa-fw fa-save',
    label: t('save'),
    disabled: !props.saveCorrectionEnabled,
    handleAction: () => props.saveCorrection(props.currentQuestionId),
    primary: true
  }

  const papersAction = {
    icon: 'fa fa-fw fa-list',
    label: tex('results_list'),
    disabled: !props.hasPapers,
    handleAction: '#papers'
  }

  const manualCorrectionAction = {
    icon: 'fa fa-fw fa-check-square-o',
    label: tex('manual_correction'),
    disabled: !props.hasPapers,
    handleAction: '#correction/questions'
  }

  switch (view) {
    case VIEW_EDITOR:
      return [
        testAction,
        divider,
        saveAction,
        overviewAction,
        divider,
        papersAction,
        manualCorrectionAction
      ]
    case VIEW_PLAYER:
    case VIEW_ATTEMPT_END:
      return [
        editAction,
        overviewAction,
        divider,
        papersAction,
        manualCorrectionAction
      ]
    case VIEW_PAPERS:
      return [
        testAction,
        divider,
        editAction,
        overviewAction,
        divider,
        papersAction,
        manualCorrectionAction
      ]
    case VIEW_PAPER:
      return [
        testAction,
        divider,
        editAction,
        overviewAction,
        divider,
        papersAction,
        manualCorrectionAction
      ]
    case VIEW_CORRECTION_QUESTIONS:
      return [
        overviewAction,
        divider,
        papersAction,
        manualCorrectionAction
      ]
    case VIEW_CORRECTION_ANSWERS:
      return [
        saveCorrectionAction,
        overviewAction,
        divider,
        papersAction,
        manualCorrectionAction
      ]
    case VIEW_OVERVIEW:
    default:
      return [
        testAction,
        divider,
        editAction,
        divider,
        papersAction,
        manualCorrectionAction
      ]
  }
}

function viewComponent(view, props) {
  switch (view) {
    case VIEW_EDITOR:
      return <Editor {...props}/>
    case VIEW_PLAYER:
      return <Player {...props}/>
    case VIEW_ATTEMPT_END:
      return <AttemptEnd {...props} />
    case VIEW_PAPERS:
      return <Papers {...props}/>
    case VIEW_PAPER:
      return <Paper {...props}/>
    case VIEW_CORRECTION_QUESTIONS:
      return <Questions {...props}/>
    case VIEW_CORRECTION_ANSWERS:
      return <Answers {...props}/>
    case VIEW_OVERVIEW:
    default:
      return <Overview {...props}/>
  }
}

function mapStateToProps(state) {
  return {
    alerts: select.alerts(state),
    quiz: select.quiz(state),
    steps: select.steps(state),
    viewMode: select.viewMode(state),
    editable: select.editable(state),
    empty: select.empty(state),
    published: select.published(state),
    hasPapers: select.hasPapers(state),
    hasUserPapers: select.hasUserPapers(state),
    papersAdmin: select.papersAdmin(state),
    registeredUser: select.registered(state),
    saveEnabled: select.saveEnabled(state),
    modal: select.modal(state),
    currentQuestionId: state.correction.currentQuestionId,
    saveCorrectionEnabled: correctionSelectors.hasCorrection(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    updateViewMode: mode => dispatch(actions.updateViewMode(mode)),
    saveQuiz: () => dispatch(editorActions.save()),
    createModal: (type, props, fading) => makeModal(type, props, fading, dispatch),
    showModal: (type, props) => dispatch(showModal(type, props)),
    fadeModal: () => dispatch(fadeModal()),
    saveCorrection: questionId => dispatch(correctionActions.saveCorrection(questionId))
  }
}

Quiz = connect(mapStateToProps, mapDispatchToProps)(Quiz)

export {Quiz}
