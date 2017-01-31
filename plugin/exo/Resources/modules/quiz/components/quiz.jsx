import React, {PropTypes as T} from 'react'
import {connect} from 'react-redux'
import PageHeader from './../../components/layout/page-header.jsx'
import {Loader} from './../../api/components/loader.jsx'
import {showModal, fadeModal} from './../../modal/actions'
import {makeModal} from './../../modal'
import {TopBar} from './top-bar.jsx'
import {Overview} from './../overview/overview.jsx'
import {Player} from './../player/components/player.jsx'
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
  VIEW_CORRECTION_ANSWERS
} from './../enums'

let Quiz = props =>
  <div className="page-container">
    <PageHeader title={props.quiz.title} />
    {props.isLoading &&
      <Loader />
    }
    {props.modal.type &&
      props.createModal(
        props.modal.type,
        props.modal.props,
        props.modal.fading
      )
    }
    {props.editable &&
      <TopBar {...props} id={props.quiz.id}/>
    }
    <div className="page-content">
      {viewComponent(props.viewMode, props)}
    </div>
  </div>

Quiz.propTypes = {
  isLoading: T.bool.isRequired,
  quiz: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  }).isRequired,
  steps: T.object.isRequired,
  editable: T.bool.isRequired,
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

function viewComponent(view, props) {
  switch (view) {
    case VIEW_EDITOR:
      return <Editor {...props}/>
    case VIEW_PLAYER:
      return <Player {...props}/>
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
    isLoading: select.isLoading(state),
    alerts: select.alerts(state),
    quiz: select.quiz(state),
    steps: select.steps(state),
    viewMode: select.viewMode(state),
    editable: select.editable(state),
    empty: select.empty(state),
    published: select.published(state),
    hasPapers: select.hasPapers(state),
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
