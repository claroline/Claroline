import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {Checkbox} from '#/main/app/input/components/checkbox'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/tools/parameters/store'

const ToolsTool = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('tools'),
      target: `${props.path}/`
    }]}
    subtitle={trans('tools')}
  >
    <FormData
      level={2}
      buttons={true}
      name={selectors.STORE_NAME+'.toolsConfig'}
      target={['apiv2_desktop_tools_configure']}
      sections={[]}
    >
      <div className="list-group">
        {props.tools
          .filter(t => !props.toolsConfig[t.name] || props.toolsConfig[t.name]['visible'] || !props.toolsConfig[t.name]['locked'])
          .map(tool =>
            <Checkbox
              key={tool.name}
              id={tool.name}
              className="list-group-item"
              label={trans(tool.name, {}, 'tools')}
              checked={props.toolsConfig[tool.name] && props.toolsConfig[tool.name]['visible']}
              disabled={props.toolsConfig[tool.name] && props.toolsConfig[tool.name]['locked']}
              onChange={checked => props.updateProp(`${tool.name}.visible`, checked)}
            />
          )
        }
      </div>
    </FormData>
  </ToolPage>

ToolsTool.propTypes = {
  path: T.string.isRequired,
  tools: T.array,
  toolsConfig: T.object,
  saveEnabled: T.bool,
  updateProp: T.func,
  save: T.func
}

export {
  ToolsTool
}
