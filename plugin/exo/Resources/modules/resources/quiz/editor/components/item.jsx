import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {FormSection} from '#/main/app/content/form/components/sections'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {getItem} from '#/plugin/exo/items'
import {Item as ItemTypes} from '#/plugin/exo/items/prop-types'
import {ItemIcon} from '#/plugin/exo/items/components/icon'
import {ItemEditor} from '#/plugin/exo/items/components/editor'

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
            <Fragment>
              {props.numbering &&
                <span className="h-numbering">{props.numbering}</span>
              }

              <ItemIcon name={itemDefinition.name} className="panel-title-icon" />
            </Fragment>
          }
          title={itemTitle}
          actions={props.actions}
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

  numbering: T.string,
  item: T.shape(
    ItemTypes.propTypes
  ).isRequired,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  update: T.func.isRequired
}

export {
  EditorItem
}
