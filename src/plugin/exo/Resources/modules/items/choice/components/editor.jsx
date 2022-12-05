import React, {Component, Fragment} from 'react'
import classes from 'classnames'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import set from 'lodash/set'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {NumberInput} from '#/main/app/data/types/number/components/input'

import {constants as quizConstants} from '#/plugin/exo/resources/quiz/constants'

import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'
import {ChoiceItem as ChoiceItemTypes} from '#/plugin/exo/items/choice/prop-types'
import {constants} from '#/plugin/exo/items/choice/constants'
import {utils} from '#/plugin/exo/items/choice/utils'

import ScoreRules from '#/plugin/exo/scores/rules'
import ScoreSum from '#/plugin/exo/scores/sum'

class Choice extends Component {
  constructor(props) {
    super(props)

    this.state = {showFeedback: false}
  }

  render() {
    return (
      <li
        className={classes('answer-item choice-answer-item', this.props.hasExpectedAnswers && {
          'unexpected-answer' : !this.props.checked,
          'expected-answer' : this.props.checked
        })}
      >
        {this.props.hasExpectedAnswers &&
          <input
            className="choice-item-tick"
            disabled={this.props.hasScore}
            type={this.props.multiple ? 'checkbox' : 'radio'}
            checked={this.props.checked}
            onChange={() => {
              // TODO : if not multiple we need to update other choices
              this.props.update('score', !this.props.checked ? 1 : 0)
            }}
          />
        }

        <div className="text-fields">
          <HtmlInput
            id={`choice-${this.props.id}-data`}
            value={this.props.data}
            onChange={data => this.props.update('data', data)}
            minRows={1}
          />

          {this.state.showFeedback &&
            <HtmlInput
              id={`choice-${this.props.id}-feedback`}
              className="feedback-control"
              value={this.props.feedback}
              onChange={text => this.props.update('feedback', text)}
            />
          }
        </div>

        <div className="right-controls">
          {(this.props.hasExpectedAnswers && this.props.hasScore) &&
            <NumberInput
              id={`choice-${this.props.id}-score`}
              className="score"
              value={this.props.score}
              onChange={score => this.props.update('score', score)}
            />
          }

          <Button
            id={`choice-${this.props.id}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments"
            label={trans('choice_feedback_info', {}, 'quiz')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />

          <Button
            id={`choice-${this.props.id}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            disabled={!this.props.deletable}
            icon="fa fa-fw fa-trash"
            label={trans('delete')}
            callback={this.props.delete}
            tooltip="top"
            dangerous={true}
          />
        </div>
      </li>
    )
  }
}

Choice.propTypes = {
  // from choice
  id: T.string.isRequired,
  type: T.string.isRequired,
  data: T.string.isRequired,

  // from solution
  feedback: T.string,
  score: T.number,
  checked: T.bool,

  // from item
  deletable: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  hasScore: T.bool.isRequired,
  multiple: T.bool.isRequired,

  update: T.func.isRequired,
  delete: T.func.isRequired
}

const Choices = props =>
  <Fragment>
    <ul className={classes('choice-answer-items', props.direction)}>
      {props.choices.map(choice =>
        <Choice
          key={choice.id}
          id={choice.id}
          type={choice.type}
          data={choice.data}
          score={choice.score}
          feedback={choice.feedback}
          checked={choice.checked}

          multiple={props.multiple}
          hasExpectedAnswers={props.hasExpectedAnswers}
          hasScore={props.hasAnswerScores}
          deletable={2 < props.choices.length}

          update={(prop, value) => props.updateChoice(choice, prop, value)}
          delete={() => props.deleteChoice(choice)}
        />
      )}
    </ul>

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-block"
      icon="fa fa-fw fa-plus"
      label={trans('add_choice', {}, 'quiz')}
      callback={props.addChoice}
    />
  </Fragment>

Choices.propTypes = {
  direction: T.oneOf(['horizontal', 'vertical']).isRequired,
  multiple: T.bool.isRequired,
  hasAnswerScores: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,

  choices: T.arrayOf(T.shape({
    // from choice
    id: T.string.isRequired,
    type: T.string.isRequired,
    data: T.string.isRequired,

    // from solution
    feedback: T.string,
    score: T.number,
    checked: T.bool
  })),

  addChoice: T.func.isRequired,
  updateChoice: T.func.isRequired,
  deleteChoice: T.func.isRequired
}

const ChoiceEditor = props => {
  const choices = utils.setChoiceTicks(props.item.choices.map(choice => {
    const solution = props.item.solutions.find(solution => solution.id === choice.id)

    return {
      id: choice.id,
      type: choice.type,
      data: choice.data,
      feedback: get(solution, 'feedback'),
      score: get(solution, 'score')
    }
  }), props.item.multiple)

  const ChoicesComponent = (
    <Choices
      direction={props.item.direction}
      multiple={props.item.multiple}
      hasExpectedAnswers={props.item.hasExpectedAnswers}
      hasAnswerScores={props.hasAnswerScores}

      choices={choices}

      addChoice={() => {
        const newChoices = props.item.choices.slice()
        const newSolutions = props.item.solutions.slice()

        const choiceId = makeId()

        // create choice item
        newChoices.push({
          id: choiceId,
          type: 'text/html',
          data: ''
        })

        let score = 1
        if (!props.item.multiple && -1 !== newSolutions.findIndex(solution => 0 < solution.score)) {
          // there is a correct answer so all new one should be incorrect
          score = 0
        }

        // create associated solution
        newSolutions.push({
          id: choiceId,
          feedback: '',
          score: score
        })

        props.update('choices', newChoices)
        props.update('solutions', newSolutions)
      }}

      updateChoice={(choice, prop, value) => {
        if (-1 !== ['feedback', 'score'].indexOf(prop)) {
          // update solution
          const updatedSolutions = cloneDeep(props.item.solutions)

          const toUpdate = updatedSolutions.find(current => current.id === choice.id)
          if (toUpdate) {
            if ('score' === prop && !props.hasAnswerScores && !props.item.multiple && value) {
              // new expected answer, reset score for other answers
              updatedSolutions.map(solution => solution.score = 0)
            }

            set(toUpdate, prop, value)
            props.update('solutions', updatedSolutions)
          }
        } else {
          // update choice
          const updatedChoices = cloneDeep(props.item.choices)
          const toUpdate = updatedChoices.find(current => current.id === choice.id)

          if (toUpdate) {
            set(toUpdate, prop, value)
            props.update('choices', updatedChoices)
          }
        }
      }}

      deleteChoice={(choice) => {
        const updatedChoices = cloneDeep(props.item.choices)
        const updatedSolutions = cloneDeep(props.item.solutions)

        const choicePos = updatedChoices.findIndex(current => current.id === choice.id)
        if (-1 !== choicePos) {
          // remove it from choices and solutions
          updatedChoices.splice(choicePos, 1)

          const solutionPos = updatedSolutions.findIndex(current => current.id === choice.id)
          updatedSolutions.splice(solutionPos, 1)

          props.update('choices', updatedChoices)
          props.update('solutions', updatedSolutions)
        }
      }}
    />
  )
  
  return (
    <FormData
      className="choice-item choice-editor"
      embedded={true}
      name={props.formName}
      dataPart={props.path}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: '_type',
              label: trans('expected_answer', {}, 'quiz'),
              type: 'choice',
              calculated: (choiceItem) => choiceItem.multiple ? constants.CHOICE_TYPE_MULTIPLE : constants.CHOICE_TYPE_SINGLE,
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.CHOICE_TYPES
              },
              onChange: (choiceType) => {
                props.update('multiple', constants.CHOICE_TYPE_MULTIPLE === choiceType)

                if (constants.CHOICE_TYPE_SINGLE === choiceType && ScoreRules.name === get(props.item, 'score.type')) {
                  // reset score type
                  props.update('score.type', ScoreSum.name)
                }
              }
            }, {
              name: 'choices',
              label: trans('choices'),
              type: 'collection',
              required: true,
              component: ChoicesComponent,
              options: {
                minLength: 3
              }
            }, {
              name: 'direction',
              label: trans('choices_direction', {}, 'quiz'),
              type: 'choice',
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: {
                  vertical: trans('vertical', {}, 'quiz'),
                  horizontal: trans('horizontal', {}, 'quiz')
                }
              }
            }, {
              name: 'numbering',
              label: trans('choice_numbering', {}, 'quiz'),
              type: 'choice',
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: quizConstants.QUIZ_NUMBERINGS
              }
            }, {
              name: 'random',
              label: trans('shuffle_answers', {}, 'quiz'),
              help: [
                trans('shuffle_answers_help', {}, 'quiz'),
                trans('shuffle_answers_results_help', {}, 'quiz')
              ],
              type: 'boolean'
            }
          ]
        }
      ]}
    />
  )
}

implementPropTypes(ChoiceEditor, ItemEditorTypes, {
  item: T.shape(
    ChoiceItemTypes.propTypes
  ).isRequired
})

export {
  ChoiceEditor
}
