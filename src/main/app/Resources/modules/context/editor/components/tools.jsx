import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import classes from 'classnames'
import cloneDeep from 'lodash/cloneDeep'

import {trans} from '#/main/app/intl'
import {constants as actionConstants, pickActionSet, Toolbar, PromisedActionTypes} from '#/main/app/action'
import {Thumbnail} from '#/main/app/components/thumbnail'
import {EditorPage} from '#/main/app/editor'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as contextSelectors} from '#/main/app/context/store'

import {getActions} from '#/main/core/tool/utils'
import {selectors, actions} from '#/main/app/context/editor/store'
import {constants} from '#/main/app/constants'

const Tool = (props) => {
  return (
    <div className={classes('rounded border p-3 d-flex flex-row align-items-start gap-3', props.className)} role="presentation">
      <Thumbnail square={true} size="sm" color={props.color}>
        <span className={`fa fa-${props.icon}`} aria-hidden={true} />
      </Thumbnail>

      <div className="flex-fill" role="presentation">
        <div className="d-flex flex-row gap-3 align-items-center" role="presentation">
          <h4 className="h6 mb-1 flex-fill">{trans(props.name, {}, 'tools')}</h4>
          {props.actions &&
            <Toolbar
              className="my-n1 mx-n2"
              buttonName="btn btn-text-body"
              actions={pickActionSet(actionConstants.ACTION_SET_LIST, props.actions)}
              tooltip="bottom"
              size="sm"
              disabled={!props.id}
            />
          }

          <div className="form-check form-switch  mb-0 me-n2">
            <input
              id={props.name}
              className="form-check-input"
              type="checkbox"
              checked={props.enabled}
              onChange={props.toggle}
            />
          </div>

        </div>
        <p className="fs-sm text-body-secondary mb-0">{trans(props.name+'_desc', {}, 'tools')}</p>
      </div>
    </div>
  )
}

Tool.propTypes = {
  className: T.string,
  color: T.string,
  icon: T.string,
  id: T.string,
  name: T.string.isRequired,
  enabled: T.bool,
  toggle: T.func.isRequired,
  actions: T.shape(PromisedActionTypes.propTypes)
}

const Tools = (props) => {
  const currentUser = useSelector(securitySelectors.currentUser)
  const contextPath = useSelector(contextSelectors.path)

  const orderedTools = [].concat(props.tools || []).sort((a, b) => {
    if (trans(a.name, {}, 'tools') > trans(b.name, {}, 'tools')) {
      return 1
    }

    return -1
  })

  return (
    <div role="presentation">
      <h3 className="h5">{props.title}</h3>
      <ul className="list-unstyled d-flex flex-column gap-2 mb-0">
        {orderedTools.map((tool, index) =>
          <li key={tool.name}>
            <Tool
              {...tool}
              className={classes(!props.enabled && 'opacity-75')}
              enabled={props.enabled}
              toggle={() => props.toggleTool(tool)}
              color={props.enabled ? constants.COLORS[index % constants.COLORS.length] : 'var(--bs-secondary)'}
              actions={getActions([tool], {}, contextPath, currentUser, true)}
            />
          </li>
        )}
      </ul>
    </div>
  )
}

Tools.propTypes = {
  title: T.string.isRequired,
  tools: T.arrayOf(T.shape({
    id: T.string,
    icon: T.string,
    name: T.string.isRequired
  })),
  enabled: T.bool.isRequired,
  toggleTool: T.func.isRequired
}

const ContextEditorTools = () => {
  const availableTools = useSelector(selectors.availableTools)
  const enabledTools = useSelector(selectors.enabledTools)
  const disabledTools = availableTools.filter(tool => -1 === enabledTools.findIndex(t => t.name === tool.name))

  const dispatch = useDispatch()
  const toggleTool = (tool) => {
    const updatedTools = cloneDeep(enabledTools)

    const toolPos = updatedTools.findIndex(t => t.name === tool.name)
    if (-1 === toolPos) {
      updatedTools.push(tool)
    } else {
      updatedTools.splice(toolPos, 1)
    }

    dispatch(actions.update(updatedTools, 'tools'))
  }

  return (
    <EditorPage
      title={trans('tools')}
      help={trans('tools_desc', {}, 'context')}
    >
      <Tools
        title={trans('enabled_tools', {}, 'context')}
        tools={enabledTools}
        enabled={true}
        toggleTool={toggleTool}
      />

      <Tools
        title={trans('disabled_tools', {}, 'context')}
        tools={disabledTools}
        enabled={false}
        toggleTool={toggleTool}
      />
    </EditorPage>
  )
}

export {
  ContextEditorTools
}
