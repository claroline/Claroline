import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import uniqBy from 'lodash/uniqBy'

import {trans} from '#/main/app/intl/translation'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'
import {ContentTitle} from '#/main/app/content/components/title'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections} from '#/main/app/content/form/components/sections'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {MODAL_ITEM_CREATION} from '#/plugin/exo/items/modals/creation'
import {MODAL_ITEM_IMPORT} from '#/plugin/exo/items/modals/import'
import {MODAL_ITEM_POSITION} from '#/plugin/exo/resources/quiz/editor/modals/item-position'
import {getNumbering, refreshIdentifiers} from '#/plugin/exo/resources/quiz/utils'
import {EditorItem} from '#/plugin/exo/resources/quiz/editor/components/item'

// TODO : lock edition of protected items

const EditorStep = props =>
  <Fragment>
    <ContentTitle
      level={3}
      displayLevel={2}
      numbering={getNumbering(props.numberingType, props.index)}
      title={props.title || trans('step', {number: props.index + 1}, 'quiz')}
      actions={props.actions}
    />

    <FormData
      level={3}
      displayLevel={2}
      embedded={true}
      name={props.formName}
      dataPart={props.path}
      sections={[
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
        size="lg"
        icon="fa fa-question"
        title={trans('no_item_info', {}, 'quiz')}
      />
      }

      {0 !== props.items.length &&
      <FormSections level={3}>
        {props.items.map((item, itemIndex) =>
          <EditorItem
            id={item.id}
            key={itemIndex}
            formName={props.formName}
            path={`${props.path}.items[${itemIndex}]`}
            errors={get(props.errors, `items[${itemIndex}]`)}

            enableScores={props.hasExpectedAnswers}
            numbering={getNumbering(props.questionNumberingType, props.index, itemIndex)}
            item={item}
            update={(prop, value) => props.update(prop ? `items[${itemIndex}].${prop}`:`items[${itemIndex}]`, value)}
            actions={[
              {
                name: 'lock',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-lock',
                label: trans('lock', {}, 'actions'),
                callback: () => true,
                disabled: true, // TODO : restore
                group: trans('management')
              }, {
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
                    callback: () => {
                      refreshIdentifiers(item).then(item => {
                        props.copyItem(item, position)
                      })
                    }
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

      <div className="component-container">
        <Button
          type={MODAL_BUTTON}
          className="btn btn-block btn-emphasis"
          icon="fa fa-fw fa-plus"
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
          className="btn btn-block"
          icon="fa fa-fw fa-upload"
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
    </FormData>
  </Fragment>

EditorStep.propsTypes = {
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

EditorStep.defaultProps = {
  actions: [],
  items: []
}

export {
  EditorStep
}
