import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import uniqBy from 'lodash/uniqBy'

import {trans} from '#/main/app/intl/translation'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormSections} from '#/main/app/content/form/components/sections'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {MODAL_ITEM_CREATION} from '#/plugin/exo/items/modals/creation'
import {MODAL_ITEM_IMPORT} from '#/plugin/exo/items/modals/import'
import {MODAL_ITEM_POSITION} from '#/plugin/exo/resources/quiz/editor/modals/item-position'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {EditorItem} from '#/plugin/exo/resources/quiz/editor/components/item'
import {EditorPage} from '#/main/app/editor'

const QuizEditorStep = props =>
  <EditorPage
    title={
      <>
        {getNumbering(props.numberingType, props.index)}
        {props.title || trans('step', {number: props.index + 1}, 'quiz')}
      </>
    }

    dataPart={props.path}
    definition={[
      {
        icon: 'fa fa-fw fa-circle-info',
        title: trans('information'),
        fields: [
          {
            name: 'title',
            label: trans('title'),
            type: 'string',
            placeholder: trans('step', {number: props.index + 1}, 'quiz')
          }, {
            name: 'description',
            label: trans('description'),
            type: 'html'
          }
        ]
      }
    ]}
  >
    {0 === props.items.length &&
      <ContentPlaceholder
        className="mb-3"
        size="lg"
        title={trans('no_item_info', {}, 'quiz')}
      />
    }

    {0 !== props.items.length &&
      <FormSections level={3} className="mb-3">
        {props.items.map((item, itemIndex) =>
          <EditorItem
            id={item.id}
            key={item.id}
            formName={props.formName}
            path={`${props.path}.items[${itemIndex}]`}
            errors={get(props.errors, `items[${itemIndex}]`)}

            enableScores={props.hasExpectedAnswers}
            numbering={getNumbering(props.questionNumberingType, props.index, itemIndex)}
            item={item}
            update={(prop, value) => props.update(prop ? `items[${itemIndex}].${prop}`:`items[${itemIndex}]`, value)}
            actions={[
              {
                name: 'copy',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-clone',
                label: trans('copy', {}, 'actions'),
                modal: [MODAL_ITEM_POSITION, {
                  icon: 'fa fa-fw fa-arrows',
                  title: trans('copy'),
                  step: {
                    id: props.id,
                    title: props.title || trans('step', {number: props.index + 1}, 'quiz')
                  },
                  steps: (props.steps || []).map((s, i) => ({
                    id: s.id,
                    title: s.title || trans('step', {number: i + 1}, 'quiz'),
                    items: s.items
                  })),
                  items: (props.items || []).map((s, i) => ({
                    id: s.id,
                    title: s.title || trans('item', {number: i + 1}, 'quiz')
                  })),
                  item: item,
                  selectAction: (position) => ({
                    type: CALLBACK_BUTTON,
                    label: trans('copy', {}, 'actions'),
                    callback: () => props.copyItem(item, position)
                  })
                }],
                group: trans('management')
              }, {
                name: 'move',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-arrows',
                label: trans('move', {}, 'actions'),
                modal: [MODAL_ITEM_POSITION, {
                  icon: 'fa fa-fw fa-arrows',
                  title: trans('movement'),
                  step: {
                    id: props.id,
                    title: props.title || trans('step', {number: props.index + 1}, 'quiz')
                  },
                  steps: (props.steps || []).map((s, i) => ({
                    id: s.id,
                    title: s.title || trans('step', {number: i + 1}, 'quiz'),
                    items: s.items
                  })),
                  items: (props.items || []).map((s, i) => ({
                    id: s.id,
                    title: s.title || trans('item', {number: i + 1}, 'quiz')
                  })),
                  item: item,
                  selectAction: (position) => ({
                    type: CALLBACK_BUTTON,
                    label: trans('move', {}, 'actions'),
                    callback: () => {
                      props.moveItem(item.id, position)
                    }
                  })
                }],
                group: trans('management')
              }, {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash',
                label: trans('delete', {}, 'actions'),
                callback: () => {
                  const newItems = props.items.slice()

                  newItems.splice(itemIndex, 1)
                  props.update('items', newItems)
                },
                confirm: {
                  title: trans('deletion'),
                  //subtitle: props.item.title || trans('', {}, 'question_types'),
                  message: trans('remove_item_confirm_message', {}, 'quiz')
                },
                dangerous: true,
                group: trans('management')
              }
            ]}
          />
        )}
      </FormSections>
    }

    <div className="d-grid gap-1 mb-3">
      <Button
        type={MODAL_BUTTON}
        variant="btn"
        size="lg"
        label={trans('add_question_from_new', {}, 'quiz')}
        modal={[MODAL_ITEM_CREATION, {
          create: (item) => {
            if (!props.hasExpectedAnswers) {
              item.hasExpectedAnswers = false
            }

            if (!props.hasExpectedAnswers || !props.score || 'none' === props.score.type) {
              item.score = {
                type: 'none'
              }
            }

            props.update('items', [].concat(props.items, [item]))
          }
        }]}
        primary={true}
      />

      <Button
        type={MODAL_BUTTON}
        variant="btn"
        label={trans('add_question_from_existing', {}, 'quiz')}
        modal={[MODAL_ITEM_IMPORT, {
          selectAction: (items) => ({
            type: CALLBACK_BUTTON,
            callback: () => {
              // append some quiz parameters to the item
              items = items.map(item => {
                if (!props.hasExpectedAnswers) {
                  item.hasExpectedAnswers = false
                }

                if (!props.hasExpectedAnswers || !props.score || 'none' === props.score.type) {
                  item.score = {
                    type: 'none'
                  }
                }

                return item
              })

              props.update('items', uniqBy([].concat(props.items, items), (item) => item.id))
            }
          })
        }]}
      />
    </div>
  </EditorPage>

QuizEditorStep.propsTypes = {
  formName: T.string.isRequired,
  path: T.string.isRequired,
  numberingType: T.string.isRequired,
  questionNumberingType: T.string.isRequired,
  steps: T.arrayOf(T.shape({
    // TODO : prop types
  })),
  moveItem: T.func.isRequired,
  copyItem: T.func.isRequired,

  index: T.number.isRequired,
  id: T.string.isRequired,
  title: T.string,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  hasExpectedAnswers: T.bool.isRequired,
  score: T.shape({
    type: T.string.isRequired
  }),
  items: T.arrayOf(T.shape({
    // TODO : prop types
  })),
  errors: T.object,
  update: T.func.isRequired
}

QuizEditorStep.defaultProps = {
  actions: [],
  items: []
}

export {
  QuizEditorStep
}
