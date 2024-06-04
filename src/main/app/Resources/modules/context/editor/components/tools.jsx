import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import classes from 'classnames'
import cloneDeep from 'lodash/cloneDeep'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {selectors, actions} from '#/main/app/context/editor/store'

const ContextEditorTools = () => {
  const availableTools = useSelector(selectors.availableTools)
  const enabledTools = useSelector(selectors.enabledTools)

  const dispatch = useDispatch()
  const toggleTool = (toolName) => {
    const updatedTools = cloneDeep(enabledTools)

    const toolPos = updatedTools.findIndex(t => t.name === toolName)
    if (-1 === toolPos) {
      updatedTools.push({name: toolName})
    } else {
      updatedTools.splice(toolPos, 1)
    }

    dispatch(actions.update(updatedTools, 'tools'))
  }

  return (
    <EditorPage
      title={trans('tools')}
      help={trans('Choisissez et configurez les outils à activer en fonction des fonctionnalités dont vous avez besoin.')}
    >
      <ul className="list-group mb-3">
        {availableTools
          .map((tool) => {
            const toolEnabled = -1 !== enabledTools.findIndex(t => t.name === tool.name)

            return (
              <li className={classes('list-group-item d-flex gap-3 align-items-start', toolEnabled && 'list-group-item-primary')} key={tool.name}>
                <span className={classes(`fs-4 fa fa-fw fa-${tool.icon} m-1`, toolEnabled ? 'text-primary' : 'text-secondary')}/>

                <div className="flex-fill" role="presentation">
                  <span className="fw-medium">{trans(tool.name, {}, 'tools')}</span>
                  <p className={classes('mb-0', toolEnabled ? 'text-primary' : 'text-secondary')}>{trans(tool.name + '_desc', {}, 'tools')}</p>
                </div>

                <div className="form-check form-switch align-self-start">
                  <input
                    id={tool.name}
                    className="form-check-input"
                    type="checkbox"
                    checked={toolEnabled}
                    disabled={tool.required}
                    onChange={() => toggleTool(tool.name)}
                  />
                </div>
              </li>
            )
          })
        }
      </ul>
    </EditorPage>
  )
}

export {
  ContextEditorTools
}
