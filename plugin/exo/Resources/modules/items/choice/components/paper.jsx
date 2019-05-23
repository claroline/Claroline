import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {HtmlText} from '#/main/core/layout/components/html-text'

import {SCORE_FIXED, SCORE_RULES} from '#/plugin/exo/quiz/enums'
import {utils} from '#/plugin/exo/items/choice/utils'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {SolutionScore} from '#/plugin/exo/components/score'
import {AnswerStats} from '#/plugin/exo/items/components/stats'
import {PaperTabs} from '#/plugin/exo/items/components/paper-tabs'
import {WarningIcon} from '#/plugin/exo/items/choice/components/warning-icon'
import {ChoiceItem as ChoiceItemTypes} from '#/plugin/exo/items/choice/prop-types'

const ChoicePaper = props =>
  <PaperTabs
    id={props.item.id}
    showExpected={props.showExpected}
    showStats={props.showStats}
    showYours={props.showYours}
    yours={
      <div className="choice-paper">
        <div className={classes('choice-answer-items', props.item.direction)}>
          {props.item.solutions.map(solution =>
            <label
              key={utils.answerId(solution.id)}
              htmlFor={utils.answerId(solution.id)}
              className={classes('answer-item choice-answer-item', utils.getAnswerClassForSolution(solution, props.answer, props.item.hasExpectedAnswers))}
            >
              {utils.isSolutionChecked(solution, props.answer) && props.item.hasExpectedAnswers ?
                <WarningIcon className="choice-item-tick" solution={solution} answers={props.answer}/> :

                <input
                  id={utils.answerId(solution.id)}
                  className="choice-item-tick"
                  name={utils.answerId(props.item.id)}
                  type={props.item.multiple ? 'checkbox': 'radio'}
                  checked={utils.isSolutionChecked(solution, props.answer)}
                  disabled={true}
                />
              }

              <HtmlText className="choice-item-content">
                {utils.getChoiceById(props.item.choices, solution.id).data}
              </HtmlText>

              <div className="choice-item-feedback">
                <Feedback
                  id={`${solution.id}-feedback`}
                  feedback={solution.feedback}
                />
              </div>

              {props.showScore && -1 === [SCORE_FIXED, SCORE_RULES].indexOf(props.item.score.type) &&
                <SolutionScore score={solution.score} />
              }
            </label>
          )}
        </div>
      </div>
    }
    expected={
      <div className="choice-paper">
        <div className={classes('choice-answer-items', props.item.direction)}>
          {props.item.solutions.map(solution =>
            <label
              key={utils.expectedId(solution.id)}
              htmlFor={utils.expectedId(solution.id)}
              className={classes('answer-item choice-answer-item', {
                'selected-answer': solution.score > 0
              })}
            >
              <input
                className="choice-item-tick"
                checked={solution.score > 0}
                id={utils.expectedId(solution.id)}
                name={utils.expectedId(props.item.id)}
                type={props.item.multiple ? 'checkbox': 'radio'}
                disabled
              />

              <HtmlText className="choice-item-content">
                {utils.getChoiceById(props.item.choices, solution.id).data}
              </HtmlText>

              <div className="choice-item-feedback">
                <Feedback
                  id={`${solution.id}-feedback-expected`}
                  feedback={solution.feedback}
                />
              </div>

              {props.showScore && -1 === [SCORE_FIXED, SCORE_RULES].indexOf(props.item.score.type) &&
                <SolutionScore score={solution.score} />
              }
            </label>
          )}
        </div>
      </div>
    }
    stats={props.showStats ?
      <div className="choice-paper">
        <div className={classes('choice-answer-items', props.item.direction)}>
          {props.item.solutions.map(solution =>
            <label
              key={solution.id}
              className={classes('answer-item choice-answer-item', props.item.hasExpectedAnswers && {
                'selected-answer': solution.score > 0
              })}
            >
              <HtmlText className="choice-item-content">
                {utils.getChoiceById(props.item.choices, solution.id).data}
              </HtmlText>

              <AnswerStats stats={{
                value: props.stats.choices[solution.id] ?
                  props.stats.choices[solution.id] :
                  0,
                total: props.stats.total
              }} />
            </label>
          )}

          <label className='answer-item choice-answer-item unanswered-item'>
            <div className="choice-item-content">
              {trans('unanswered', {}, 'quiz')}
            </div>

            <AnswerStats stats={{
              value: props.stats.unanswered ? props.stats.unanswered : 0,
              total: props.stats.total
            }} />
          </label>
        </div>
      </div> :
      <div></div>
    }
  />

ChoicePaper.propTypes = {
  item: T.shape(
    ChoiceItemTypes.propTypes
  ).isRequired,
  answer: T.array,
  showScore: T.bool.isRequired,
  showYours: T.bool.isRequired,
  showExpected: T.bool.isRequired,
  showStats: T.bool.isRequired,
  stats: T.shape({
    choices: T.object,
    unanswered: T.number,
    total: T.number
  })
}

ChoicePaper.defaultProps = {
  answer: []
}

export {
  ChoicePaper
}
