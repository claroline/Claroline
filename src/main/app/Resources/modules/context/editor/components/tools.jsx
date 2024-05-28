import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {useSelector} from 'react-redux'
import {selectors} from '#/main/app/context/editor/store'

const ContextEditorTools = () => {
  const availableTools = useSelector(selectors.availableTools)
  const enabledTools = useSelector(selectors.enabledTools)

  console.log(availableTools)
  console.log(enabledTools)

  return (
    <EditorPage
      title={trans('tools')}
      help={trans('Choisissez et configurez les outils à activer en fonction des fonctionnalités dont vous avez besoin.')}
    >
      <ul className="list-group mb-3">
        {availableTools
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
                  checked={-1 !== enabledTools.findIndex(t => t.name === tool.name)}
                  disabled={tool.required}
                  onChange={e => props.onChange(e.target.checked)}
                />
              </div>
            </li>
          )
        }
      </ul>
    </EditorPage>
  )
}

export {
  ContextEditorTools
}
