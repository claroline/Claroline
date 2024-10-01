import React from 'react'
import get from 'lodash/get'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {selectors} from '#/main/transfer/tools/import/store'
import {selectors as formSelectors} from '#/main/app/content/form'

const ImportEditorOverview = () => {
  const formData = useSelector(state => formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)))
  const isNew = useSelector(state => formSelectors.isNew(formSelectors.form(state, selectors.FORM_NAME)))
  const explanation = useSelector(state => selectors.importExplanation(state))

  let entity = typeof formData.action !== 'undefined' ? formData.action.substring(0, formData.action.indexOf('_')) : formData.type
  let action = typeof formData.action !== 'undefined' ? formData.action.substring(formData.action.indexOf('_') + 1) : formData.action
  if(typeof formData.type !== 'undefined' && formData.type !== entity) {
    entity = formData.type
    action = ''
  }

  return (
    <EditorPage
      title={trans('overview')}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'type',
              type: 'choice',
              label: trans('type'),
              disabled: !isNew,
              calculated: () => entity,
              options: {
                noEmpty: false,
                condensed: true,
                choices: Object.keys(explanation).sort().reduce((o, key) => Object.assign(o, {
                  [key]: trans(key, {}, 'transfer')
                }), {})
              }
            },{
              name: 'action',
              type: 'choice',
              label: trans('action'),
              disabled: !isNew,
              displayed: !!entity,
              calculated: () => entity + '_' + action,
              options: {
                noEmpty: false,
                condensed: true,
                choices: Object.keys(get(explanation, entity, [])).reduce((o, key) => Object.assign(o, {
                  [entity + '_' + key]: trans(key, {}, 'transfer')
                }), {})
              }
            }, {
              name: 'name',
              type: 'string',
              label: trans('name')
            }, {
              name: 'file',
              type: 'file',
              label: trans('file'),
              disabled: !isNew
            }
          ]
        }
      ]}
    />
  )
}

export {
  ImportEditorOverview
}
