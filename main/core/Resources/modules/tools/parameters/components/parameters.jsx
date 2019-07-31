
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {trans} from '#/main/app/intl/translation'
import {selectors} from '#/main/core/tools/parameters/store'
import {Checkbox} from '#/main/app/input/components/checkbox'

import {FormData} from '#/main/app/content/form/containers/data'

const ToolComponent = (props) =>
  <FormData
    level={2}
    buttons={true}
    name={selectors.STORE_NAME+'.toolsConfig'}
    title={trans('parameters')}
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

ToolComponent.propTypes = {
  tools: T.array,
  toolsConfig: T.object,
  saveEnabled: T.bool,
  updateProp: T.func,
  save: T.func
}

const Parameters = connect(
  (state) => ({
    tools: selectors.tools(state),
    toolsConfig: formSelect.data(formSelect.form(state, selectors.STORE_NAME+'.toolsConfig')),
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME+'.toolsConfig'))
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.STORE_NAME+'.toolsConfig', propName, propValue))
    }
  })
)(ToolComponent)

export {
  Parameters
}
