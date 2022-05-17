import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {selectors} from '#/plugin/home/tools/home/editor/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'
import {getFormDataPart} from '#/plugin/home/tools/home/editor/utils'

const ToolShortcutTabParameters = (props) =>
  <FormData
    embedded={true}
    disabled={props.readOnly}
    name={selectors.FORM_NAME}
    dataPart={`${getFormDataPart(props.currentTab.id, props.tabs)}.parameters`}
    sections={[
      {
        title: trans('general'),
        fields: [
          {
            name: 'tool',
            type: 'choice',
            label: trans('tool'),
            required: true,
            options: {
              choices: props.tools
                .filter(tool => tool.name !== 'home')
                .reduce((acc, current) => Object.assign({}, acc, {
                  [current.name]: trans(current.name, {}, 'tools')
                }), {})
            }
          }
        ]
      }
    ]}
  />

ToolShortcutTabParameters.propTypes = {
  readOnly: T.bool,
  currentTab: T.shape(
    TabTypes.propTypes
  ),
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  tools: T.arrayOf(T.shape({
    name: T.string.isRequired
  })),
  update: T.func.isRequired
}

export {
  ToolShortcutTabParameters
}
