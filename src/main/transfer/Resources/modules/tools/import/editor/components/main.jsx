import React from 'react'
import get from 'lodash/get'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Editor} from '#/main/app/editor/components/main'
import {selectors} from '#/main/transfer/tools/import/store'
import {ImportEditorFormat} from '#/main/transfer/tools/import/editor/components/format'
import {ImportEditorActions} from '#/main/transfer/tools/import/editor/components/actions'
import {ImportEditorPlaning} from '#/main/transfer/tools/import/editor/components/planing'
import {ImportEditorOverview} from '#/main/transfer/tools/import/editor/components/overview'
import {ImportEditorExamples} from '#/main/transfer/tools/import/editor/components/examples'

const ImportEditor = (props) => {
  const entity = props.formData.action.substring(0, props.formData.action.indexOf('_'))
  const action = props.formData.action.substring(props.formData.action.indexOf('_') + 1)

  return (
    <Editor
      path={props.path + '/edit'}
      title={get(props.formData, 'name')}
      name={selectors.FORM_NAME}
      target={['apiv2_transfer_import_update', {id: props.formData.id}]}
      close={props.path}
      defaultPage="overview"
      actionsPage={ImportEditorActions}
      overviewPage={ImportEditorOverview}
      pages={[
        {
          name:'format',
          title: trans('format'),
          render: () => (
            <ImportEditorFormat
              schema={get(props.explanation, entity+'.'+action, {})}
            />
          )
        }, {
          name:'examples',
          title: trans('examples', {}, 'transfer'),
          render: () => (
            <ImportEditorExamples
              samples={get(props.samples, entity+'.'+action, {})}
              format={props.formData.format}
              entity={entity}
              action={action}
            />
          )
        }, {
          name:'planing',
          title: trans('planing', {}, 'scheduler'),
          disabled: props.schedulerEnabled === false,
          render: () => (
            <ImportEditorPlaning
              schedulerEnabled={props.schedulerEnabled}
            />
          )
        }
      ].concat(props.pages || [])}
    />
  )
}

ImportEditor.propTypes = {
  path: T.string.isRequired,
  formData: T.shape({
    id: T.string,
    name: T.string,
    action: T.string,
    format: T.string
  }),
  explanation: T.object.isRequired,
  samples: T.object.isRequired,
  schedulerEnabled: T.bool.isRequired,
  pages: T.array
}

export {
  ImportEditor
}
