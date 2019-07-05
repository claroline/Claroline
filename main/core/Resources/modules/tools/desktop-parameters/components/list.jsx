
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'
import {selectors} from '#/main/core/tools/desktop-parameters/store'
import {
  PageContainer,
  PageHeader,
  PageActions,
  PageAction
} from '#/main/core/layout/page'
import {Checkbox} from '#/main/app/input/components/checkbox'

const ToolComponent = (props) =>
  <PageContainer>
    <PageHeader
      title={trans('parameters', {}, 'tools')}
    >
      <PageActions>
        <PageAction
          id="save"
          type={CALLBACK_BUTTON}
          label={trans('save')}
          icon="fa fa-fw fa-save"
          disabled={!props.saveEnabled}
          primary={true}
          callback={() => props.save()}
        />
      </PageActions>
    </PageHeader>
    <div className="list-group" fill={true}>
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
  </PageContainer>

ToolComponent.propTypes = {
  tools: T.array,
  toolsConfig: T.object,
  saveEnabled: T.bool,
  updateProp: T.func,
  save: T.func
}

const List = connect(
  (state) => ({
    tools: selectors.tools(state),
    toolsConfig: formSelect.data(formSelect.form(state, 'toolsConfig')),
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'toolsConfig'))
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp('toolsConfig', propName, propValue))
    },
    save() {
      dispatch(formActions.save('toolsConfig', ['apiv2_desktop_tools_configure']))
    }
  })
)(ToolComponent)

export {
  List
}
