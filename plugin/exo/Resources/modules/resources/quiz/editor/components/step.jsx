import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {MODAL_ITEM_CREATION} from '#/plugin/exo/items/modals/creation'
import {MODAL_ITEM_IMPORT} from '#/plugin/exo/items/modals/import'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {EditorItem} from '#/plugin/exo/resources/quiz/editor/components/item'
import {selectors} from '#/plugin/exo/resources/quiz/editor/store/selectors'

// TODO : lock edition of protected items

const EditorStep = props => {
  const numbering = getNumbering(props.numbering, props.index)

  return (
    <Fragment>
      <h3 className="h2 step-title">
        {numbering &&
          <span className="h-numbering">{numbering}</span>
        }

        {props.title || trans('step', {number: props.index + 1}, 'quiz')}

        {0 !== props.actions.length &&
          <Toolbar
            id={props.id}
            className="step-toolbar"
            buttonName="btn"
            tooltip="bottom"
            toolbar="more"
            size="sm"
            actions={props.actions}
          />
        }
      </h3>

      <FormData
        level={3}
        displayLevel={2}
        embedded={true}
        name={props.formName}
        dataPart={props.path}
        sections={[
          {
            icon: 'fa fa-fw fa-info',
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
                type: 'string',
                options: {
                  long: true
                }
              }
            ]
          }
        ]}
      >
        {0 === props.items.length &&
          <EmptyPlaceholder
            size="lg"
            icon="fa fa-question"
            title={trans('no_item_info', {}, 'quiz')}
            help={trans('no_item_help', {}, 'quiz')}
          />
        }

        {0 !== props.items.length &&
          <FormSections level={3}>
            {props.items.map((item, itemIndex) =>
              <EditorItem
                id={item.id}
                key={item.id}
                formName={props.formName}
                path={`${props.path}.items[${itemIndex}]`}

                index={itemIndex}
                item={item}
                update={(prop, value) => props.update(`items[${itemIndex}].${prop}`, value)}
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
              create: (item) => props.update('items', [].concat(props.items, [item]))
            }]}
            primary={true}
          />

          <Button
            type={MODAL_BUTTON}
            className="btn btn-block"
            icon="fa fa-fw fa-upload"
            label={trans('add_question_from_existing', {}, 'quiz')}
            modal={[MODAL_ITEM_IMPORT, {
              // TODO : removes duplicates items
              import: (items) => props.update('items', [].concat(props.items, items))
            }]}
          />
        </div>
      </FormData>
    </Fragment>
  )
}

EditorStep.propsTypes = {
  formName: T.string.isRequired,
  path: T.string.isRequired,

  index: T.number.isRequired,
  numbering: T.string.isRequired,
  title: T.string,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
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
