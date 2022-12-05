import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {LinkButton} from '#/main/app/buttons/link'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ContentHtml} from '#/main/app/content/components/html'

import {selectors as correctionSelectors} from '#/plugin/exo/resources/quiz/correction/store/selectors'

export const QuestionRow = props =>
  <tr>
    <td>
      <ContentHtml>{props.question.title || props.question.content}</ContentHtml>
    </td>
    <td>{props.answers.length}</td>
    <td className="actions-cell text-right">
      <TooltipOverlay
        id={props.question.id}
        tip={trans('correct', {}, 'actions')}
      >
        <LinkButton
          className="btn btn-link"
          target={`${props.path}/correction/${props.question.id}`}
        >
          <span className="fa fa-fw fa-check-square" />
        </LinkButton>
      </TooltipOverlay>
    </td>
  </tr>

QuestionRow.propTypes = {
  path: T.string.isRequired,
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

const QuestionsComponent = props =>
  props.questions.length > 0 ?
    <div className="questions-list">
      <table className="table table-striped table-hover">
        <thead>
          <tr>
            <th>{trans('question', {}, 'quiz')}</th>
            <th>{trans('number_of_papers_to_correct', {}, 'quiz')}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {props.questions.map((question, idx) =>
            <QuestionRow
              key={idx}
              path={props.path}
              {...question}
            />
          )}
        </tbody>
      </table>
    </div> :
    <div className="questions-list">
      <div className="alert alert-warning">
        {trans('no_question_to_correct', {}, 'quiz')}
      </div>
    </div>

QuestionsComponent.propTypes = {
  path: T.string.isRequired,
  questions: T.arrayOf(T.object).isRequired
}

QuestionsComponent.defaultProps = {
  questions: []
}

const Questions = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    questions: correctionSelectors.questions(state)
  })
)(QuestionsComponent)

export {Questions}