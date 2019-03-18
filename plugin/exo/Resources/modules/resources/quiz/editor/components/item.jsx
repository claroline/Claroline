import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {FormSection} from '#/main/app/content/form/components/sections'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {getItem} from '#/plugin/exo/items'
import {Item as ItemTypes} from '#/plugin/exo/items/prop-types'
import {ItemIcon} from '#/plugin/exo/items/components/icon'
import {ItemEditor} from '#/plugin/exo/items/components/editor'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'

const EditorItem = props =>
  <Await
    for={getItem(props.item.type)}
    placeholder={
      <div className="panel panel-default">
        <div className="panel-heading">
          <span className="fa fa-fw fa-spinner fa-spin icon-with-text-right" />
          {trans('loading')}
        </div>
      </div>
    }

    then={(itemDefinition) => {
      const itemTitle = props.item.title || trans(itemDefinition.name, {}, 'question_types')

      return (
        <FormSection
          {...omit(props, 'formName', 'path', 'index', 'item', 'update')}
          id={props.item.id}
          className="embedded-form-section"
          icon={
            <ItemIcon name={itemDefinition.name} className="panel-title-icon" />
          }
          title={itemTitle}
          actions={[
            {
              name: 'copy',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-clone',
              label: trans('copy', {}, 'actions'),
              modal: [],
              group: trans('management')
            }, {
              name: 'move',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-arrows',
              label: trans('move', {}, 'actions'),
              modal: [],
              group: trans('management')
            }, {
              name: 'share',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-share',
              label: trans('share', {}, 'actions'),
              modal: [],
              group: trans('community')
            }, {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash-o',
              label: trans('delete', {}, 'actions'),
              callback: () => true,
              confirm: {
                title: trans('deletion'),
                subtitle: itemTitle,
                message: trans('remove_item_confirm_message', {}, 'quiz')
              },
              dangerous: true,
              group: trans('management')
            }
          ]}
        >
          <ItemEditor
            embedded={true}
            formName={props.formName}
            path={props.path}
            disabled={false}

            definition={itemDefinition}
            item={props.item}
            update={props.update}
          />
        </FormSection>
      )
    }}
  />

EditorItem.propTypes = {
  formName: T.string.isRequired,
  path: T.string.isRequired,

  index: T.number.isRequired,
  item: T.shape(
    ItemTypes.propTypes
  ).isRequired,

  update: T.func.isRequired
}

export {
  EditorItem
}
