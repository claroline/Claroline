import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormSections} from '#/main/app/content/form/components/sections'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {EditorPage} from '#/main/app/editor'

import {selectors as editorSelectors} from '#/main/core/resource/editor/store'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {EditorItem} from '#/plugin/exo/resources/quiz/editor/components/item'
import {Item as ItemTypes} from '#/plugin/exo/items/prop-types'

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
              actions={props.getItemActions(item, itemIndex)}
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
  items: T.arrayOf(T.shape(
    ItemTypes.propTypes
  )),
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
