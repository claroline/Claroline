import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'

import {selectors} from '#/main/app/context/editor/store'
import {FormContent} from '#/main/app/content/form/containers/content'

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

const EditorOverview = (props) => {
  return (
    <>
      <FormContent
        name={selectors.FORM_NAME}
        autoFocus={true}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'poster',
                type: 'image',
                hideLabel: true,
                label: trans('poster')
              }, {
                name: 'name',
                type: 'string',
                label: trans('name'),
                required: true
              }, {
                name: 'code',
                type: 'string',
                label: trans('code'),
                required: true
              }
            ]
          }, {
            title: trans('advanced'),
            primary: true,
            fields: [
              {
                name: 'meta.description',
                type: 'string',
                label: trans('description'),
                options: {
                  long: true
                }
              }, {
                name: 'tags',
                label: trans('tags'),
                type: 'tag'
              }
            ]
          }
        ]}
      />
      {/*<ContextTools
        tools={props.tools}
        availableTools={props.availableTools}
        onChange={() => true}
      />*/}
    </>
  )
}

EditorOverview.propTypes = {
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
  getAvailableTools: T.func.isRequired
}

export {
  EditorOverview
}
