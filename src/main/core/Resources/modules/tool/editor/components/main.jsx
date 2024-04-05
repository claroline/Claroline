import React from 'react'

import {trans} from '#/main/app/intl'
import {FormData} from '#/main/app/content/form/containers/data'

import {ToolPage} from '#/main/core/tool'
import {selectors} from '#/main/core/tool/editor/store'

const ToolEditor = (props) =>
  <ToolPage
    title={trans('parameters')}
  >
    <FormData
      className="mt-3"
      name={selectors.STORE_NAME}
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
              name: 'thumbnail',
              label: trans('thumbnail'),
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
              label: trans('resource_showIcon', {}, 'resource'),
              type: 'boolean'
            }, {
              name: 'display.fullscreen',
              label: trans('resource_fullscreen', {}, 'resource'),
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
  </ToolPage>

ToolEditor.propTypes = {

}

export {
  ToolEditor
}
