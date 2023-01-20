import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {getPlainText} from '#/main/app/data/types/html/utils'

import {getItems} from '#/plugin/exo/items'
import {Icon as ItemIcon} from '#/plugin/exo/items/components/icon'

const ItemList = props => {
  const [itemDefinitions, setItemDefinitions] = useState([])

  useEffect(() => {
    getItems().then((response) => {
      setItemDefinitions(response)
    })
  }, [isEmpty(itemDefinitions)])

  return (
    <ListData
      name={props.name}
      delete={props.delete}
      fetch={{
        url: ['apiv2_quiz_questions_list'],
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
          name: 'tags',
          type: 'tag',
          label: trans('tags'),
          displayable: true,
          displayed: true,
          sortable: false,
          options: {
            objectClass: 'UJM\\ExoBundle\\Entity\\Item\\Item'
          }
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

      actions={props.actions}
    />
  )
}


ItemList.propTypes = {
  name: T.string.isRequired,
  delete: T.object,
  actions: T.func
}

export {
  ItemList
}
