import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

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

const WorkspaceEditorOverview = () => {
  return (
    <EditorPage
      title={trans('overview')}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'data.poster',
              type: 'poster',
              label: trans('poster'),
              hideLabel: true
            }, {
              name: 'data.name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'data.code',
              type: 'string',
              label: trans('code'),
              required: true
            }
          ]
        }, {
          title: trans('further_information'),
          subtitle: trans('further_information_help'),
          primary: true,
          fields: [
            {
              name: 'data.meta.description',
              type: 'string',
              label: trans('description_short'),
              help: trans('Décrivez succintement votre espace d\'activités (La description courte est affichée dans les listes et sur la vue "À propos").'),
              options: {
                long: true,
                minRows: 2
              }
            }, {
              name: 'data.meta.descriptionHtml',
              label: trans('description_long'),
              type: 'html',
              help: [
                trans('Décrivez de manière détaillée le contenu de votre espace d\'activités, la travail attendu par vos utilisateurs (La description détaillée est affichée sur la vue "À propos" à la place de la description courte).'),
              ]
            }, {
              name: 'data.tags',
              label: trans('tags'),
              type: 'tag'
            }
          ]
        }
      ]}
    >
      {/*<ContextTools
        tools={props.tools}
        availableTools={props.availableTools}
        onChange={() => true}
      />*/}
    </EditorPage>
  )
}

export {
  WorkspaceEditorOverview
}
