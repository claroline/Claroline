import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {ListForm} from '#/main/app/content/list/parameters/containers/form'
import {EditorPage} from '#/main/app/editor'

import entriesSource from '#/plugin/claco-form/data/sources/entries'
import {selectors} from '#/plugin/claco-form/resources/claco-form/editor/store'
import isEmpty from 'lodash/isEmpty'

const generateDisplayList = (fields = []) => {
  const displayList = {
    title: trans('title'),
    date: trans('date'),
    user: trans('user'),
    categories: trans('categories'),
    keywords: trans('keywords', {}, 'clacoform')
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
              name: 'random.enabled',
              type: 'boolean',
              label: trans('label_random_enabled', {}, 'clacoform'),
              linked: [
                {
                  name: 'random.categories',
                  type: 'choice',
                  label: trans('label_random_categories', {}, 'clacoform'),
                  displayed: (clacoForm) => !isEmpty(categories) && get(clacoForm, 'random.enabled', false),
                  options: {
                    multiple: true,
                    condensed: false,
                    inline: false,
                    choices: categories ? categories.reduce((acc, cat) => Object.assign(acc, {
                      [cat.id]: cat.name
                    }), {}) : {}
                  }
                }, {
                  name: 'random.dates',
                  type: 'date-range',
                  label: trans('label_random_dates', {}, 'clacoform'),
                  displayed: (clacoForm) => get(clacoForm, 'random.enabled', false)
                }
              ]
            }, {
              name: 'details.search_enabled',
              type: 'boolean',
              label: trans('label_search_enabled', {}, 'clacoform')
            }, {
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
              name: 'details.display_subtitle',
              type: 'choice',
              label: trans('field_for_subtitle', {}, 'clacoform'),
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
        list={entriesSource(clacoForm, true, true, true)}
        parameters={clacoForm.list}
      />
    </EditorPage>
  )
}

export {
  EditorList
}