import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Editor} from '#/main/app/editor/components/main'

import {selectors} from '#/main/app/context/editor/store'

const ContextTools = (props) =>
  <ul className="list-group mb-3">
    {props.availableTools
      .map((tool) =>
        <li className="list-group-item d-flex gap-3 align-items-center" key={tool.name}>
          <span className={`fs-4 fa fa-fw fa-${tool.icon}`} />
          <div className="flex-fill" role="presentation">
            <span className="fw-medium">{trans(tool.name, {}, 'tools')}</span>
            <p className="mb-0 text-secondary">{trans(tool.name+'_desc', {}, 'tools')}</p>
          </div>

          <div className="form-check form-switch align-self-start">
            <input
              id={tool.name}
              className="form-check-input"
              type="checkbox"
              checked={-1 !== props.tools.indexOf(t => t.name === tool.name)}
              disabled={tool.required}
              onChange={e => props.onChange(e.target.checked)}
            />
          </div>
        </li>
      )
    }
  </ul>

ContextTools.propTypes = {
  tools: T.arrayOf(T.shape({

  })).isRequired,
  availableTools: T.arrayOf(T.shape({
    icon: T.string,
    name: T.string.isRequired,
    required: T.bool.isRequired
  })),
  onChange: T.func.isRequired
}

const ContextEditor = (props) => {
  useEffect(() => {
    props.getAvailableTools(props.contextName, props.contextId)
    props.openEditor(props.contextData)
  }, [props.contextName, props.contextId])

  return (
    <Editor
      path={props.path+'/edit'}
      title={trans(props.contextName, {}, 'context') || props.title}
      name={selectors.FORM_NAME}
      onSave={(savedData) => props.refresh(props.name, savedData, props.contextType)}
      target={['claro_context_configure', {
        context: props.contextName,
        contextId: props.contextId
      }]}
      canAdministrate={true}
      close={props.path}
      overviewPage={props.overviewPage}
      appearancePage={props.appearancePage}
      historyPage={props.historyPage}
      actionsPage={props.actionsPage}
      defaultPage="overview"
      pages={props.pages}
    />
  )
}

ContextEditor.propTypes = {
  path: T.string.isRequired,
  contextName: T.string.isRequired,
  contextId: T.string,
  tools: T.arrayOf(T.shape({

  })).isRequired,
  availableTools: T.arrayOf(T.shape({
    icon: T.string,
    name: T.string.isRequired,
    required: T.bool.isRequired
  })),
  getAvailableTools: T.func.isRequired,
  openEditor: T.func.isRequired,
  refresh: T.func.isRequired
}

export {
  ContextEditor
}
