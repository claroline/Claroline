import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {constants as quizConstants} from '#/plugin/exo/resources/quiz/constants'

import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'
import {constants} from '#/plugin/exo/items/choice/constants'

const Choice = props =>
  <li className="choice-item answer-item">
  </li>

const Choices = props =>
  <div className="choice-items">
    <ul>
      {props.choices.map(choice =>
        <Choice
          key={choice.id}
          id={choice.id}
          data={choice.data}
          score={choice._score}
          feedback={choice._feedback}
          multiple={props.item.multiple}
          fixedScore={-1 < [SCORE_FIXED, SCORE_RULES].indexOf(props.item.score.type)}
          checked={choice._checked}
          deletable={choice._deletable}
          onChange={props.onChange}
        />
      )}
    </ul>

    {get(props.item, '_errors.choices') &&
      <ContentError error={props.item._errors.choices} warnOnly={!props.validating}/>
    }

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-block"
      icon="fa fa-fw fa-plus"
      label={trans('add_choice', {}, 'quiz')}
      callback={props.addChoice}
    />
  </div>

Choices.propTypes = {
  choices: T.arrayOf(T.shape({

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
            name: 'multiple',
            label: trans('expected_answer', {}, 'quiz'),
            type: 'choice',
            calculated: (choiceItem) => choiceItem.multiple ? constants.CHOICE_TYPE_MULTIPLE : constants.CHOICE_TYPE_SINGLE,
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: constants.CHOICE_TYPES
            },
            onChange: (choiceType) => props.update('multiple', constants.CHOICE_TYPE_MULTIPLE === choiceType)
          }, {
            name: 'choices',
            label: trans('choices'),
            required: true,
            render: (choiceItem, choiceErrors) => {
              return (
                <div>choice list</div>
                /*<Choices
                  addChoice={(choice) => {
                    //
                    const newChoices = props.item.choices.slice()
                    newChoices.push(choice)
                    //

                    props.update('choices', newChoices)
                  }}
                />*/
              )
            },
            validate: (choiceItem) => {
              return undefined
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
  item: T.shape({
    // TODO : choice item type
    multiple: T.bool
  }).isRequired
})

export {
  ChoiceEditor
}
