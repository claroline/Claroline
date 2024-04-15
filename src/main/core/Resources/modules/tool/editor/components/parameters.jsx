import React from 'react'
import {PropTypes as T} from 'prop-types'
import {selectors} from '#/main/core/tool/editor/store'
import {trans} from '#/main/app/intl'
import {FormData} from '#/main/app/content/form/containers/data'

const EditorParameters = (props) => {
  return (
    <FormData
      className="my-3"
      name={selectors.STORE_NAME}
      dataPart="data"
      target={['apiv2_tool_configure', {
        name: props.name,
        context: props.contextType,
        contextId: props.contextId
      }]}
      onSave={(savedData) => props.refresh(props.name, savedData, props.contextType)}
      buttons={true}
      definition={[
        {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'poster',
              label: trans('poster'),
              type: 'image'
            }, {
              name: 'display.order',
              type: 'number',
              label: trans('order'),
              options: {
                min: 0
              }
            }, {
              name: 'display.showIcon',
              label: trans('show_icon', {}, 'tools'),
              type: 'boolean'
            }, {
              name: 'restrictions.hidden',
              type: 'boolean',
              label: trans('restrict_hidden')
            }
          ]
        }
      ]}
    />
  )
}

EditorParameters.propTypes = {

}

export {
  EditorParameters
}
