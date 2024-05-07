import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {PropTypes as T} from 'prop-types'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'
import {ResourceEditorAppearance} from '#/main/core/resource/editor'

const ToolEditorAppearance = (props) =>
  <EditorPage
    title={trans('appearance')}
    help={trans('Personnalisez les paramètres d\'affichage avancés de votre outil et de son contenu.')}
    definition={[
      {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        primary: true,
        fields: [
          {
            name: 'data.poster',
            label: trans('poster'),
            type: 'image'
          }
        ]
      }, {
        title: trans('advanced'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'data.restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden'),
            help: trans('restrict_hidden_help')
          }
        ]
      }
    ].concat(props.definition || [])}
  >
    {props.children}
  </EditorPage>

ToolEditorAppearance.propTypes = {
  definition: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )),
  children: T.any
}

export {
  ToolEditorAppearance
}
