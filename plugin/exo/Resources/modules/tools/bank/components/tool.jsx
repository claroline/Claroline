import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ListData} from '#/main/app/content/list/containers/data'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {getPlainText} from '#/main/app/data/types/html/utils'

import {getItems} from '#/plugin/exo/items'
import {Icon as ItemIcon} from '#/plugin/exo/items/components/icon'

// TODO : restore list grid display
// TODO : find better than the Await wrapper to load items

const BankTool = props =>
  <ToolPage>
    <Await
      for={getItems()}
      then={itemDefinitions => {
        return (
          <ListData
            name="questions"
            fetch={{
              url: ['question_list'],
              autoload: true
            }}
            definition={[
              {
                name: 'type',
                label: trans('type'),
                displayed: true,
                alias: 'mimeType',
                render: (rowData) => {
                  const itemType = itemDefinitions.find(type => type.type === rowData.type)
                  // variable is for eslint rule "Component definition is missing display name  react/display-name"
                  const itemIcon = <ItemIcon name={itemType.name} />

                  return itemIcon
                },
                type: 'choice',
                options: {
                  choices: itemDefinitions
                    .reduce((selectObj, itemType) => Object.assign(selectObj, {
                      [itemType.type]: trans(itemType.name, {}, 'question_types')
                    }), {})
                }
              }, {
                name: 'content',
                label: trans('question', {}, 'quiz'),
                type: 'string',
                render: (rowData) => {
                  if (rowData.title) {
                    return rowData.title
                  } else {
                    const content = getPlainText(rowData.content)

                    return 50 < content.length ? `${content.substr(0, 50)}...` : content
                  }
                },
                displayed: true
              }, {
                name: 'meta.created',
                label: trans('creation_date'),
                type: 'date',
                alias: 'dateCreate'
              }, {
                name: 'meta.updated',
                label: trans('last_modification'),
                type: 'date',
                alias: 'dateModify',
                displayed: true,
                options: {
                  time: true
                }
              }, {
                name: 'selfOnly',
                label: trans('filter_by_self_only', {}, 'quiz'),
                type: 'boolean',
                displayable: false
              }
            ]}

            actions={(rows) => [
              /*{
               icon: 'fa fa-fw fa-copy',
               label: trans('duplicate'),
               action: (rows) => props.duplicateQuestions(rows, false)
               }, {
               icon: 'fa fa-fw fa-clone',
               label: trans('duplicate_model'),
               action: (rows) => props.duplicateQuestions(rows, true)
               },*/ {
                // TODO : checks if the current user has the rights to share to enable the action
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-share',
                label: trans('share', {}, 'actions'),
                callback: () => props.shareQuestions(rows)
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete', {}, 'actions'),
                callback: () => props.removeQuestions(rows),
                dangerous: true
              }
            ]}
          />
        )
      }}
    />
  </ToolPage>

BankTool.propTypes = {
  removeQuestions: T.func.isRequired,
  duplicateQuestions: T.func.isRequired,
  shareQuestions: T.func.isRequired
}

export {
  BankTool
}
