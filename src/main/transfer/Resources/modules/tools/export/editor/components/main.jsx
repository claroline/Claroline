import React from 'react'
import get from 'lodash/get'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Editor} from '#/main/app/editor/components/main'
import {selectors} from '#/main/transfer/tools/export/store'
import {ExportEditorFormat} from '#/main/transfer/tools/export/editor/components/format'
import {ExportEditorActions} from '#/main/transfer/tools/export/editor/components/actions'
import {ExportEditorPlaning} from '#/main/transfer/tools/export/editor/components/planing'
import {ExportEditorOverview} from '#/main/transfer/tools/export/editor/components/overview'

const ExportEditor = (props) => {
  const entity = props.formData.action.substring(0, props.formData.action.indexOf('_'))
  const action = props.formData.action.substring(props.formData.action.indexOf('_') + 1)

  return (
    <Editor
      path={props.path + '/edit'}
      title={get(props.formData, 'name')}
      name={selectors.FORM_NAME}
      target={['apiv2_transfer_export_update', {id: props.formData.id}]}
      close={props.path}
      defaultPage="overview"
      actionsPage={ExportEditorActions}
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
  explanation: T.object.isRequired,
  updateProp: T.func.isRequired,
  schedulerEnabled: T.bool.isRequired,
  pages: T.array
}

export {
  ExportEditor
}
