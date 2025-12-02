import React from 'react'
import {useSelector} from 'react-redux'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {ListForm} from '#/main/app/content/list/parameters/containers/form'
import {EditorPage} from '#/main/app/editor'

import entriesSource from '#/plugin/claco-form/data/sources/entries'
import {selectors} from '#/plugin/claco-form/resources/claco-form/editor/store'

const generateDisplayList = (fields = []) => {
  const displayList = {
    title: trans('title'),
    date: trans('date'),
    user: trans('user'),
    categories: trans('categories')
  }

  fields.map(field => {
    displayList[field.id] = field.label
  })

  return displayList
}

const EditorList = () => {
  const clacoForm = useSelector(selectors.clacoForm)
  const categories = useSelector(selectors.categories)

  return (
    <EditorPage
      title={trans('entries_list_search', {}, 'clacoform')}
      dataPart="resource"
      definition={[
        {
          id: 'general',
          title: trans('general'),
          fields: [
            {
              name: 'details.display_title',
              type: 'choice',
              label: trans('field_for_title', {}, 'clacoform'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: generateDisplayList(clacoForm.fields)
              }
            }, {
              name: 'details.display_content',
              type: 'choice',
              label: trans('field_for_content', {}, 'clacoform'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: generateDisplayList(clacoForm.fields)
              }
            }
          ]
        }
      ]}
    >
      <ListForm
        level={3}
        name={selectors.STORE_NAME}
        dataPart="resource.list"
        list={entriesSource(merge({categories: categories}, clacoForm), true, true, true)}
        parameters={clacoForm.list}
      />
    </EditorPage>
  )
}

export {
  EditorList
}