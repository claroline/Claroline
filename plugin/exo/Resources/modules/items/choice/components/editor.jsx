import React, {Component, Fragment} from 'react'
import classes from 'classnames'
import get from 'lodash/get'
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
            callback={() => this.props.delete()}
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
          data={choice.data}
          score={choice.score}
          feedback={choice.feedback}
          checked={choice._checked}

          multiple={props.multiple}
          fixedScore={props.fixedScore}
          deletable={2 < props.choices.length}

          update={props.updateChoice}
          delete={props.deleteChoice}
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
    score: T.number
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
            render: (choiceItem, choiceErrors) => {
              return (
                <Choices
                  direction={choiceItem.direction}
                  multiple={choiceItem.multiple}
                  fixedScore={-1 < [ScoreFixed.name, ScoreRules.name].indexOf(get(choiceItem, 'score.type'))}

                  choices={choiceItem.choices.map(choice => ({
                    id: choice.id,
                    data: choice.data
                  }))}

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

                    // create associated solution
                    newSolutions.push({
                      id: choiceId,
                      feedback: '',
                      score: 0
                    })

                    props.update('choices', newChoices)
                    props.update('solutions', newSolutions)
                  }}

                  updateChoice={(prop, value) => {

                  }}

                  deleteChoice={(prop, value) => {

                  }}
               />
              )
            },
            validate: (choiceItem) => {
              return undefined
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
