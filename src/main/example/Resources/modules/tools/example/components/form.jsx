import React from 'react'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/example//tools/example/store/selectors'

const ExampleForm = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: 'Forms',
        target: props.path+'/forms'
      }
    ]}
    subtitle="Forms"
  >
    <FormData
      name={selectors.FORM_NAME}
      definition={[
        {
          id: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'text',
              label: 'Short text',
              type: 'string',
              required: true,
              help: 'My help text',
              placeholder: 'My placeholder'
            }, {
              name: 'longText',
              label: 'Long text',
              type: 'string',
              placeholder: 'My placeholder',
              options: {long: true}
            }, {
              name: 'htmlText',
              label: 'HTML text',
              type: 'html',
              placeholder: 'My placeholder'
            }, {
              name: 'file',
              label: 'File upload',
              type: 'file'
            }, {
              name: 'boolean',
              label: 'This checkbox will display additional fields once checked.',
              type: 'boolean',
              linked: [
                {
                  name: 'anotherText',
                  label: 'Another text',
                  type: 'string',
                  displayed: (data) => !!data.boolean
                },
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'image',
              label: 'image',
              type: 'image'
            }, {
              name: 'icon',
              label: 'FontAwesome icon',
              help: trans('resource_showIcon_help', {}, 'resource'),
              type: 'boolean'
            }, {
              name: 'color',
              label: 'Color',
              type: 'boolean'
            }, {
              name: 'display.fullscreen',
              label: trans('resource_fullscreen', {}, 'resource'),
              type: 'boolean'
            }
          ]
        }
      ]}
    />
  </ToolPage>

export {
  ExampleForm
}
