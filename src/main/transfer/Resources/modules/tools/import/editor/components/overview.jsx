import React from 'react'
import get from 'lodash/get'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {selectors} from '#/main/transfer/tools/import/store'
import {selectors as formSelectors} from '#/main/app/content/form'

const ImportEditorOverview = () => {
  const formData = useSelector(state => formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)))
  const explanation = useSelector(state => selectors.importExplanation(state))
  const entity = formData.action.substring(0, formData.action.indexOf('_'))

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
              type: 'string',
              label: trans('type'),
              hideLabel: true,
              disabled: true,
              calculated: (formData) => trans(formData.action.substring(0, formData.action.indexOf('_')))
            },{
              name: 'action',
              type: 'choice',
              label: trans('action'),
              disabled: true,
              options: {
                noEmpty: true,
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
              disabled: true
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
