import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {ThumbnailBox} from './thumbnail-box.jsx'
import {QuizEditor} from './quiz-editor.jsx'
import {StepEditor} from './step-editor.jsx'
import {actions} from './../actions'
import {TYPE_QUIZ, TYPE_STEP} from './../../enums'
import select from './../selectors'
import {CustomDragLayer} from './../../../utils/custom-drag-layer.jsx'

let Editor = props =>
  <div className="quiz-editor">
    <ThumbnailBox
      thumbnails={props.thumbnails}
      validating={props.validating}
      onThumbnailClick={props.selectObject}
      onThumbnailMove={props.moveStep}
      onNewStepClick={props.createStep}
      onStepDeleteClick={props.deleteStepAndItems}
      showModal={props.showModal}
    />
    <div className="edit-zone user-select-disabled">
      {selectSubEditor(props)}
    </div>
    <CustomDragLayer/>
  </div>

Editor.propTypes = {
  thumbnails: T.array.isRequired,
  validating: T.bool.isRequired,
  selectObject: T.func.isRequired,
  moveStep: T.func.isRequired,
  createStep: T.func.isRequired,
  deleteStepAndItems: T.func.isRequired,
  showModal: T.func.isRequired
}

function selectSubEditor(props) {
  switch (props.currentObject.type) {
    case TYPE_QUIZ:
      return (
        <QuizEditor
          quiz={props.quizProperties}
          validating={props.validating}
          updateProperties={props.updateQuiz}
          activePanelKey={props.activeQuizPanel}
          handlePanelClick={props.selectQuizPanel}
        />
      )
    case TYPE_STEP:
      return (
        <StepEditor
          step={props.currentObject}
          mandatoryQuestions={props.quizProperties.parameters.mandatoryQuestions}
          validating={props.validating}
          updateStep={props.updateStep}
          activePanelKey={props.activeStepPanel}
          handlePanelClick={props.selectStepPanel}
          handleItemDeleteClick={props.deleteStepItem}
          handleItemMove={props.moveItem}
          handleItemCreate={props.createItem}
          handleMoveQuestionStepClick={props.moveQuestionStep}
          handleItemUpdate={props.updateItem}
          handleItemHintsUpdate={props.updateItemHints}
          handleItemDetailUpdate={props.updateItemDetail}
          handleItemsImport={props.importItems}
          handleContentItemCreate={props.createContentItem}
          handleContentItemUpdate={props.updateContentItem}
          handleContentItemDetailUpdate={props.updateContentItemDetail}
          handleFileUpload={props.saveContentItemFile}
          showModal={props.showModal}
          closeModal={props.fadeModal}
        />
      )
  }
  throw new Error(`Unkwnown type ${props.currentObject}`)
}

selectSubEditor.propTypes = {
  activeQuizPanel: T.string.isRequired,
  selectQuizPanel: T.func.isRequired,
  updateQuiz: T.func.isRequired,
  quizProperties: T.object.isRequired,
  currentObject: T.shape({
    type: T.string.isRequired
  }).isRequired,
  updateStep: T.string.isRequired,
  activeStepPanel: T.string.isRequired,
  selectStepPanel: T.func.isRequired,
  validating: T.bool.isRequired,
  deleteStepItem: T.func.isRequired,
  moveItem: T.func.isRequired,
  createItem: T.func.isRequired,
  updateItem: T.func.isRequired,
  updateItemHints: T.func.isRequired,
  updateItemDetail: T.func.isRequired,
  importItems: T.func.isRequired,
  createContentItem: T.func.isRequired,
  updateContentItem: T.func.isRequired,
  updateContentItemDetail: T.func.isRequired,
  moveQuestionStep: T.func.isRequired,
  saveContentItemFile: T.func,
  showModal: T.func.isRequired,
  fadeModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    thumbnails: select.thumbnails(state),
    currentObject: select.currentObjectDeep(state),
    activeQuizPanel: select.quizOpenPanel(state),
    activeStepPanel: select.stepOpenPanel(state),
    quizProperties: select.quiz(state),
    validating: select.validating(state)
  }
}

Editor = connect(mapStateToProps, actions)(Editor)

export {Editor}
