import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'
import {EditorPage} from '#/main/app/editor'

const ResourceEditorAppearance = (props) =>
  <EditorPage
    title={trans('appearance')}
    help={trans('Personnalisez les paramètres d\'affichage avancés de votre ressource et de son contenu.')}
    definition={[
      {
        name: 'images',
        icon: 'fa fa-fw fa-picture',
        title: trans('images'),
        primary: true,
        fields: [
          {
            name: 'resourceNode.poster',
            label: trans('poster'),
            type: 'image'
          }, {
            name: 'resourceNode.thumbnail',
            label: trans('thumbnail'),
            type: 'image'
          }
        ]
      }, {
        title: trans('advanced'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'resourceNode.restrictions.hidden',
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

ResourceEditorAppearance.propTypes = {
  definition: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )),
  children: T.any
}

export {
  ResourceEditorAppearance
}
