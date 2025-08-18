import React, {createElement, useCallback, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans, transChoice} from '#/main/app/intl'
import {makeId} from '#/main/app/utils/id'
import {FormModal} from '#/main/app/data/modals/form/components/modal'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form'
import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {NumberInput} from '#/main/app/data/types/number/components/input'
import {DragNDropContext} from '#/main/app/overlays/dnd'

import {Item} from '#/plugin/exo/items/prop-types'
import {getItem} from '#/plugin/exo/items'
import ScoreNone from '#/plugin/exo/scores/none'
import {ItemIcon} from '#/plugin/exo/items/components/icon'
import {calculateTotal} from '#/plugin/exo/items/score'
import {Badge} from '#/main/app/components/badge'
import {DragDropProvider} from '#/main/app/overlays/dnd/components/provider'
import {CustomDragLayer} from '#/plugin/exo/utils/custom-drag-layer'
import {validate} from '#/plugin/exo/items/validation'

const FORM_NAME = 'quizItemForm'

const ItemFormModalComponent = (props) => {
  const dispatch = useDispatch()

  const [itemDefinition, setItemDefinition] = useState(null)
  useEffect(() => {
    if (props.item.type) {
      getItem(props.item.type).then(setItemDefinition)
    }
  }, [props.item.type])

  const formData = useSelector((state) => formSelectors.data(formSelectors.form(state, FORM_NAME)))
  const update = useCallback((prop, value) => {
    dispatch(formActions.updateProp(FORM_NAME, prop, value))
  }, [FORM_NAME])
  const setErrors = useCallback((errors) => {
    dispatch(formActions.setErrors(FORM_NAME, errors))
  }, [FORM_NAME])

  let supportedScores, currentScore, availableScores, itemScore
  if (get(itemDefinition, 'answerable')) {
    supportedScores = [ScoreNone].concat(itemDefinition.supportScores(props.item) || [])

    currentScore = supportedScores.find(score => score.name === get(formData, 'score.type'))
    availableScores = supportedScores.reduce((scoreChoices, current) => Object.assign(scoreChoices, {
      [current.name]: current.meta.label
    }), {})

    itemScore = calculateTotal(formData)
  }

  return (
    <FormModal
      {...omit(props, 'item', 'enableScores')}
      className="quiz-item item-editor"
      name={FORM_NAME}
      icon={!props.isNew && get(itemDefinition, 'name') ?
        <ItemIcon name={itemDefinition.name} className="icon-with-text-right" size="xs" /> :
        undefined
      }
      title={props.isNew ?
        trans(get(itemDefinition, 'answerable') ? 'new_question' : 'new_content', {}, 'quiz') :
        <div className="w-100 d-flex flex-row justify-content-between align-items-center" role="presenation">
          {trans(get(itemDefinition, 'name'), {}, 'question_types')}
          {(itemScore || 0 === itemScore) ?
            <Badge variant="primary" subtle={true} className="fs-base mx-2">
              {transChoice('solution_score', itemScore, {score: itemScore}, 'quiz')}
            </Badge> :
            <Badge variant="secondary" subtle={true} className="fs-base">
              {trans('score_none', {}, 'quiz')}
            </Badge>
          }
        </div>

      }
      subtitle={props.isNew ? trans(get(itemDefinition, 'answerable') ? 'new_question_desc' : 'new_content_desc', {}, 'quiz') : undefined}
      data={props.item}
      validate={validate}
      saveLabel={props.isNew ?
        trans(get(itemDefinition, 'answerable') ? 'add_question' : 'add_content', {}, 'actions') :
        trans(get(itemDefinition, 'answerable') ? 'save_question' : 'save_content', {}, 'actions')
      }
      size="lg"
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'content',
              label: trans('question', {}, 'quiz'),
              type: 'html',
              required: true,
              displayed: get(itemDefinition, 'answerable')
            }, {
              name: 'objects',
              label: trans('question_objects', {}, 'quiz'),
              type: 'medias',
              displayed: get(itemDefinition, 'answerable')
            }, {
              name: 'hasExpectedAnswers',
              label: trans('has_expected_answers', {}, 'quiz'),
              type: 'boolean',
              displayed: props.enableScores && get(itemDefinition, 'answerable'),
              help: [
                trans('has_expected_answers_help', {}, 'quiz'),
                trans('has_expected_answers_help_score', {}, 'quiz')
              ],
              onChange: (checked) => {
                if (!checked) {
                  update('score.type', ScoreNone.name)
                }
              }
            }
          ]
        }, {
          title: trans('custom'),
          primary: true,
          fill: true,
          hideTitle: true,
          render: () => itemDefinition && createElement(itemDefinition.components.editor, {
            formName: FORM_NAME,
            item: formData,
            hasAnswerScores: get(itemDefinition, 'answerable') && props.enableScores ? currentScore.hasAnswerScores: false,
            update: update,
            setErrors: setErrors
          })
        }, {
          icon: 'fa fa-fw fa-circle-info',
          title: trans('information'),
          fields: [
            {
              name: 'title',
              label: trans('title'),
              type: 'string'
            }, {
              name: 'description',
              label: trans('description'),
              type: 'html'
            }, {
              name: 'tags',
              label: trans('tags'),
              type: 'tag'
            }
          ]
        }, {
          icon: 'fa fa-fw fa-percentage',
          title: trans('score'),
          displayed: props.enableScores && get(itemDefinition, 'answerable') && formData.hasExpectedAnswers,
          fields: [
            {
              name: 'score.type',
              label: trans('calculation_mode', {}, 'quiz'),
              type: 'choice',
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                // get the list of score supported by the current type
                choices: availableScores
              },
              linked: currentScore ? currentScore
                // generate the list of fields for the score type
                .configure(get(formData, 'score'), (prop, value) => update(`score.${prop}`, value))
                .map(scoreProp => Object.assign({}, scoreProp, {
                  name: `score.${scoreProp.name}`,
                  // slightly ugly because I only support 1 level
                  linked: scoreProp.linked ? scoreProp.linked.map(linkedProp => Object.assign({}, linkedProp, {
                    name: `score.${linkedProp.name}`
                  })) : []
                })) : []
            }
          ]
        }, {
          id: 'help',
          icon: 'fa fa-fw fa-circle-question',
          title: trans('help'),
          displayed: get(itemDefinition, 'answerable'),
          fields: [
            {
              name: 'hints',
              label: trans('hints', {}, 'quiz'),
              type: 'collection',
              options: {
                placeholder: trans('no_hint_info', {}, 'quiz'),
                button: trans('add_hint', {}, 'quiz'),
                defaultItem: {id: makeId(), penalty: 0},
                render: useCallback((hint = {}, hintErrors, hintIndex) => (
                  <div className="hint-control">
                    <HtmlInput
                      id={`hint-${props.item.id}-${hintIndex}-text`}
                      className="hint-value"
                      value={hint.value}
                      onChange={value => update(`hints[${hintIndex}].value`, value)}
                    />

                    <NumberInput
                      id={`hint-${props.item.id}-${hintIndex}-penalty`}
                      className="hint-penalty"
                      min={0}
                      value={hint.penalty}
                      onChange={value => update(`hints[${hintIndex}].penalty`, value)}
                    />
                  </div>
                ), [props.item.id])
              }
            }, {
              name: 'feedback',
              label: trans('feedback', {}, 'quiz'),
              type: 'html'
            }
          ]
        }
      ]}
    >
      <DragDropProvider>
        <CustomDragLayer key="drag-layer" />
      </DragDropProvider>
    </FormModal>
  )
}

ItemFormModalComponent.propTypes = {
  isNew: T.bool,
  item: T.shape(
    Item.propTypes
  ).isRequired,
  enableScores: T.bool,
  onSave: T.func
}

const ItemFormModal = new DragNDropContext(ItemFormModalComponent)

export {
  ItemFormModal
}
