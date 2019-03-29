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

import ScoreFixed from '#/plugin/exo/scores/fixed'
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
        className={classes('answer-item choice-answer-item', {
          'unexpected-answer' : !this.props.checked,
          'expected-answer' : this.props.checked
        })}
      >
        <input
          className="choice-item-tick"
          disabled={!this.props.fixedScore}
          type={this.props.multiple ? 'checkbox' : 'radio'}
          checked={this.props.checked}
          onChange={() => {
            // TODO : if not multiple we need to update other choices
            this.props.update('score', !this.props.checked ? 1 : 0)
          }}
        />

        <div className="text-fields">
          <HtmlInput
            id={`choice-${this.props.id}-data`}
            value={this.props.data}
            onChange={data => this.props.update('data', data)}
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
          {!this.props.fixedScore &&
            <NumberInput
              id={`choice-${this.props.id}-score`}
              className="choice-score"
              value={this.props.score}
              onChange={score => this.props.update('score', score)}
            />
          }

          <Button
            id={`choice-${this.props.id}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments-o"
            label={trans('choice_feedback_info', {}, 'quiz')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />

          <Button
            id={`choice-${this.props.id}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            disabled={!this.props.deletable}
            icon="fa fa-fw fa-trash-o"
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
  fixedScore: T.bool.isRequired,
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
          fixedScore={props.fixedScore}
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
  fixedScore: T.bool.isRequired,

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

const ChoiceEditor = props =>
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
            required: true,
            render: (choiceItem) => {
              const fixedScore = -1 < [ScoreFixed.name, ScoreRules.name].indexOf(get(choiceItem, 'score.type'))
              const choices = utils.setChoiceTicks(choiceItem.choices.map(choice => {
                const solution = choiceItem.solutions.find(solution => solution.id === choice.id)

                return {
                  id: choice.id,
                  type: choice.type,
                  data: choice.data,
                  feedback: get(solution, 'feedback'),
                  score: get(solution, 'score')
                }
              }), choiceItem.multiple)

              const ChoicesComponent = (
                <Choices
                  direction={choiceItem.direction}
                  multiple={choiceItem.multiple}
                  fixedScore={fixedScore}

                  choices={choices}

                  addChoice={() => {
                    const newChoices = choiceItem.choices.slice()
                    const newSolutions = choiceItem.solutions.slice()

                    const choiceId = makeId()

                    // create choice item
                    newChoices.push({
                      id: choiceId,
                      type: 'text/html',
                      data: ''
                    })

                    // create associated solution
                    newSolutions.push({
                      id: choiceId,
                      feedback: '',
                      score: 0
                    })

                    props.update('choices', newChoices)
                    props.update('solutions', newSolutions)
                  }}

                  updateChoice={(choice, prop, value) => {
                    if (-1 !== ['feedback', 'score'].indexOf(prop)) {
                      // update solution
                      const updatedSolutions = cloneDeep(choiceItem.solutions)

                      const toUpdate = updatedSolutions.find(current => current.id === choice.id)
                      if (toUpdate) {
                        if ('score' === prop && fixedScore && !choiceItem.multiple && value) {
                          // new expected answer, reset score for other answers
                          updatedSolutions.map(solution => solution.score = 0)
                        }

                        set(toUpdate, prop, value)
                        props.update('solutions', updatedSolutions)
                      }
                    } else {
                      // update choice
                      const updatedChoices = cloneDeep(choiceItem.choices)
                      const toUpdate = updatedChoices.find(current => current.id === choice.id)

                      if (toUpdate) {
                        set(toUpdate, prop, value)
                        props.update('choices', updatedChoices)
                      }
                    }
                  }}

                  deleteChoice={(choice) => {
                    const updatedChoices = cloneDeep(choiceItem.choices)
                    const updatedSolutions = cloneDeep(choiceItem.solutions)

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

              return ChoicesComponent
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

implementPropTypes(ChoiceEditor, ItemEditorTypes, {
  item: T.shape(
    ChoiceItemTypes.propTypes
  ).isRequired
})

export {
  ChoiceEditor
}
