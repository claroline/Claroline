import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {tex} from '#/main/core/translation'
import {selectors as correctionSelectors} from './../selectors'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'

export const QuestionRow = props =>
  <tr>
    <td>{props.question.title || props.question.content}</td>
    <td>{props.answers.length}</td>
    <td className="table-actions text-right">
      <TooltipElement
        id={props.question.id}
        tip={tex('to_correct')}
      >
        <a className="btn btn-link-default" href={`#correction/questions/${props.question.id}`}>
          <span className="fa fa-fw fa-check-square-o" />
          <span className="sr-only">{tex('to_correct')}</span>
        </a>
      </TooltipElement>
    </td>
  </tr>

QuestionRow.propTypes = {
  question: T.shape({
    id: T.string.isRequired,
    title: T.string,
    content: T.string.isRequired,
    score: T.shape({
      type: T.string,
      max: T.number
    }).isRequired
  }).isRequired,
  answers: T.arrayOf(T.object)
}

let Questions = props =>
  props.questions.length > 0 ?
    <div className="questions-list">
      <table className="table table-striped table-hover">
        <thead>
          <tr>
            <th>{tex('question')}</th>
            <th>{tex('number_of_papers_to_correct')}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {props.questions.map((question, idx) =>
            <QuestionRow key={idx} {...question}/>
          )}
        </tbody>
      </table>
    </div> :
    <div className="questions-list">
      <div className="alert alert-warning">
        {tex('no_question_to_correct')}
      </div>
    </div>

Questions.propTypes = {
  questions: T.arrayOf(T.object).isRequired
}

function mapStateToProps(state) {
  return {
    questions: correctionSelectors.questions(state)
  }
}

const ConnectedQuestions = connect(mapStateToProps)(Questions)

export {ConnectedQuestions as Questions}