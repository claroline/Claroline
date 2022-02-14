import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListForm} from '#/main/app/content/list/parameters/containers/form'
import {LINK_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {ClacoForm as ClacoFormTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'
import entriesSource from '#/plugin/claco-form/data/sources/entries'

const generateDisplayList = (fields = []) => {
  const displayList = {
    title: trans('title'),
    date: trans('date'),
    user: trans('user'),
    categories: trans('categories'),
    keywords: trans('keywords', {}, 'clacoform')
  }

  fields.filter(f => !f.restrictions.hidden).map(field => {
    displayList[field.id] = field.label
  })

  return displayList
}

const EditorList = props =>
  <Fragment>
    <FormData
      level={2}
      title={trans('entries_list_search', {}, 'clacoform')}
      name={selectors.STORE_NAME+'.clacoFormForm'}
      buttons={true}
      target={(clacoForm) => ['apiv2_clacoform_update', {id: clacoForm.id}]}
      cancel={{
        type: LINK_BUTTON,
        target: props.path,
        exact: true
      }}
      sections={[
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
                  displayed: (clacoForm) => get(clacoForm, 'random.enabled'),
                  options: {
                    multiple: true,
                    condensed: false,
                    inline: false,
                    choices: props.clacoForm.categories ? props.clacoForm.categories.reduce((acc, cat) => Object.assign(acc, {
                      [cat.id]: cat.name
                    }), {}) : {}
                  }
                }, {
                  name: 'random.dates',
                  type: 'date-range',
                  label: trans('label_random_dates', {}, 'clacoform'),
                  displayed: (clacoForm) => clacoForm.random && clacoForm.random.enabled
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
                choices: generateDisplayList(props.clacoForm.fields)
              }
            }, {
              name: 'details.display_subtitle',
              type: 'choice',
              label: trans('field_for_subtitle', {}, 'clacoform'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: generateDisplayList(props.clacoForm.fields)
              }
            }, {
              name: 'details.display_content',
              type: 'choice',
              label: trans('field_for_content', {}, 'clacoform'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: generateDisplayList(props.clacoForm.fields)
              }
            }
          ]
        }
      ]}
    />

    <ListForm
      level={3}
      name={selectors.STORE_NAME+'.clacoFormForm'}
      dataPart="list"
      list={entriesSource(props.clacoForm, true, true, true, true).parameters}
      parameters={props.clacoForm.list}
    />
  </Fragment>

EditorList.propTypes = {
  path: T.string.isRequired,
  clacoForm: T.shape(
    ClacoFormTypes.propTypes
  )
}

export {
  EditorList
}