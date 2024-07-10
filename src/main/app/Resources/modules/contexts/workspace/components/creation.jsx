import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import pick from 'lodash/pick'

import {trans} from '#/main/app/intl'
import {makeId} from '#/main/core/scaffolding/id'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentCreation} from '#/main/app/content/components/creation'

import {MODAL_WORKSPACE_IMPORT} from '#/main/core/workspace/modals/import'
import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

const WorkspaceCreation = (props) =>
  <ContentCreation
    className="mb-3"
    types={[
      {
        id: 'create-from-model',
        icon: 'stamp',
        label: trans('Créer à partir d\'un modèle'),
        description: trans('Choisissez un modèle préconfiguré pour commencer à ajouter vos contenus plus rapidement.'),
        action: {
          type: MODAL_BUTTON,
          modal: [MODAL_WORKSPACES, {
            icon: 'fa fa-fw fa-stamp',
            title: trans('workspace_models', {}, 'workspace'),
            url: ['apiv2_workspace_list_model'],
            multiple: false,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => {
                props.startCreation(merge({}, pick(selected[0], 'name', 'code', 'thumbnail', 'poster', 'meta'), {
                  model: selected[0],
                  meta: {model: false, personal: false, archived: false}
                }), 'model')
                props.changeStep('info')
              }
            })
          }]
        }
      }, {
        id: 'create-empty',
        icon: 'book',
        label: trans('Créer un espace vide'),
        description: trans('Créez un espace vide pour pouvoir le configurer comme vous le souhaitez.'),
        action: {
          type: CALLBACK_BUTTON,
          callback: () => props.changeStep('info')
        },
        advanced: true
      }, {
        id: 'create-from-copy',
        icon: 'clone',
        label: trans('Copier un espace existant'),
        description: trans('Dupliquez un espace de la plateforme ainsi que tous ses contenus.'),
        action: {
          type: MODAL_BUTTON,
          modal: [MODAL_WORKSPACES, {
            url: ['apiv2_workspace_list_managed'],
            multiple: false,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => {
                props.startCreation(merge({}, selected[0], {
                  id: makeId(),
                  meta: {model: false, personal: false}
                }), 'copy')
                props.changeStep('info')
              }
            })
          }]
        },
        group: 'A partir d\'un contenu existant'
      }, {
        id: 'create-from-organization',
        icon: 'building',
        label: trans('Ajouter depuis une autre organization'),
        description: trans('Sélectionnez un espace existant dans une autre organization pour le rendre accessible aux membres de l\'organization <b>Organization name</b>.'),
        action: {
          type: CALLBACK_BUTTON,
          callback: () => true
        },
        group: 'A partir d\'un contenu existant'
      }, {
        id: 'create-from-import',
        icon: 'file-zipper',
        label: trans('Importer une archive'),
        description: trans('Déposez une archive (.zip) générée à partir d\'une autre plateforme compatible.'),
        action: {
          type: MODAL_BUTTON,
          //callback: () => props.changeStep('upload'),
          modal: [MODAL_WORKSPACE_IMPORT]
        },
        advanced: true,
        group: 'A partir d\'un contenu existant'
      }
    ]}
  />

WorkspaceCreation.propTypes = {
  startCreation: T.func.isRequired,
  changeStep: T.func.isRequired
}

export {
  WorkspaceCreation
}
