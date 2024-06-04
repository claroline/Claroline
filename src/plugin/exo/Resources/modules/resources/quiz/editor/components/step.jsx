import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormSections} from '#/main/app/content/form/components/sections'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {EditorPage} from '#/main/app/editor'

import {selectors as editorSelectors} from '#/main/core/resource/editor/store'
import {MODAL_ITEM_POSITION} from '#/plugin/exo/resources/quiz/editor/modals/item-position'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {EditorItem} from '#/plugin/exo/resources/quiz/editor/components/item'

const QuizEditorStep = props => {
  const resourceEditorPath = useSelector(editorSelectors.path)
  const numbering = getNumbering(props.numberingType, props.index)

  useEffect(() => {
    if (props.currentItemId) {
      setTimeout(() => {
        scrollTo('#item-'+props.currentItemId)
      }, 500)

    }
  }, [props.currentItemId])

  return (
    <EditorPage
      title={
        <>
          {numbering &&
            <span className="h-numbering">{numbering}</span>
          }

          {props.title || trans('step', {number: props.index + 1}, 'quiz')}
        </>
      }
      actions={[
        {
          name: 'summary',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-list',
          label: trans('open-summary', {}, 'actions'),
          target: resourceEditorPath+'/steps',
          exact: true
        }
      ].concat(props.actions.filter(a => !['add-item', 'import-item'].includes(a.name)))}

      dataPart={props.path}
      autoFocus={!props.currentItemId}
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
          className="mt-4"
          size="lg"
          title={trans('no_item_info', {}, 'quiz')}
        />
      }

      {0 !== props.items.length &&
        <FormSections level={3} defaultOpened={'item'+props.currentItemId} className="mt-4">
          {props.items.map((item, itemIndex) =>
            <EditorItem
              key={item.id}
              id={'item'+item.id}
              formName={props.formName}
              path={`${props.path}.items[${itemIndex}]`}
              errors={get(props.errors, `items[${itemIndex}]`)}
              autoFocus={props.currentItemId === item.id}

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

      <Toolbar
        className="d-grid gap-1 my-5"
        buttonName="btn"
        defaultName="btn-body"
        primaryName={classes('btn-primary btn-lg', {
          'btn-wave': 0 === props.items.length
        })}
        dangerousName="btn-danger"
        /*separatorName="border-top border-1 my-3"*/
        /*toolbar={`add-item import-item | `+props.actions.map(a => a.name + ' ')}*/
        actions={props.actions.filter(a => ['add-item', 'import-item'].includes(a.name)).map(a => Object.assign({}, a, {icon: null}))}
      />
    </EditorPage>
  )
}

QuizEditorStep.propsTypes = {
  formName: T.string.isRequired,
  path: T.string.isRequired,
  numberingType: T.string.isRequired,
  questionNumberingType: T.string.isRequired,
  currentItemId: T.string,
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
