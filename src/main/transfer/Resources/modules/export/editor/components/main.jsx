import React, {useEffect} from 'react'
import get from 'lodash/get'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Editor} from '#/main/app/editor/components/main'
import {selectors} from '#/main/transfer/tools/export/store'
import {ExportEditorFormat} from '#/main/transfer/export/editor/components/format'
import {ExportEditorActions} from '#/main/transfer/export/editor/components/actions'
import {ExportEditorPlaning} from '#/main/transfer/export/editor/components/planing'
import {ExportEditorOverview} from '#/main/transfer/export/editor/components/overview'

const ExportEditor = (props) => {
  const entity = typeof props.formData.action !== 'undefined' ? props.formData.action.substring(0, props.formData.action.indexOf('_')) : ''
  const action = typeof props.formData.action !== 'undefined' ? props.formData.action.substring(props.formData.action.indexOf('_') + 1) : ''

  useEffect(() => {
    if (props.isNew) {
      props.resetForm(props.contextData)
    }
  }, [props.isNew])

  return (
    <Editor
      path={props.path + (props.isNew ? '/new' : '/edit')}
      title={get(props.formData, 'name', trans('export', {}, 'transfer'))}
      name={selectors.FORM_NAME}
      target={(formData, isNew) => isNew ? ['apiv2_transfer_export_create']: ['apiv2_transfer_export_update', {id: props.formData.id}]}
      onSave={(response) => {
        props.history.push(props.path + '/' + response.id + '/edit')
        return props.onSave(response)
      }}
      close={props.path}
      defaultPage="overview"
      actionsPage={!props.isNew ? ExportEditorActions : undefined}
      overviewPage={ExportEditorOverview}
      pages={[
        {
          name:'format',
          title: trans('format'),
          render: () => (
            <ExportEditorFormat
              schema={get(props.explanation, entity+'.'+action, {})}
              columns={get(props.formData, 'extra.columns', [])}
              update={(selectedColumns) => props.updateProp('extra.columns', selectedColumns)}
            />
          )
        }, {
          name:'planing',
          title: trans('planing', {}, 'scheduler'),
          disabled: props.schedulerEnabled === false,
          render: () => (
            <ExportEditorPlaning
              schedulerEnabled={props.schedulerEnabled}
              updateProp={props.updateProp}
              isNew={props.isNew}
            />
          )
        }
      ].concat(props.pages || [])}
    />
  )
}

ExportEditor.propTypes = {
  path: T.string.isRequired,
  formData: T.shape({
    id: T.string,
    name: T.string,
    action: T.string
  }),
  pages: T.array,
  history: T.object,
  contextData: T.object,
  isNew: T.bool.isRequired,
  onSave: T.func.isRequired,
  resetForm: T.func.isRequired,
  updateProp: T.func.isRequired,
  explanation: T.object.isRequired,
  schedulerEnabled: T.bool.isRequired
}

export {
  ExportEditor
}
