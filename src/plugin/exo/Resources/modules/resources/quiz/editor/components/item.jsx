import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans, transChoice} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Await} from '#/main/app/components/await'
import {FormSection} from '#/main/app/content/form/components/sections'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {getItem} from '#/plugin/exo/items'
import {calculateTotal} from '#/plugin/exo/items/score'
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
      const itemScore = itemDefinition.answerable ? calculateTotal(props.item): null

      return (
        <FormSection
          {...omit(props, 'formName', 'path', 'index', 'item', 'update', 'enableScores')}
          id={props.item.id}
          className="embedded-form-section"
          icon={
            <Fragment>
              {props.numbering &&
                <span className="h-numbering">{props.numbering}</span>
              }

              <ItemIcon name={itemDefinition.name} className="icon-with-text-right" />
            </Fragment>
          }
          subtitle={(itemScore || 0 === itemScore) ? `(${transChoice('solution_score', itemScore, {score: itemScore}, 'quiz')})` : undefined}
          title={itemTitle}

          errors={props.errors}
          actions={props.actions}
        >
          <ItemEditor
            embedded={true}
            formName={props.formName}
            path={props.path}
            disabled={props.item.meta.protectQuestion && !hasPermission('edit', props.item)}
            enableScores={props.enableScores}
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

  enableScores: T.bool,
  numbering: T.string,
  item: T.shape(
    ItemTypes.propTypes
  ).isRequired,
  errors: T.object,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  update: T.func.isRequired
}

export {
  EditorItem
}
