import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl'
import {makeId} from '#/main/core/scaffolding/id'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Thumbnail} from '#/main/app/components/thumbnail'

import {MODAL_WORKSPACE_IMPORT} from '#/main/core/workspace/modals/import'
import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'
import pick from 'lodash/pick'

const WorkspaceCreationType = (props) =>
  <div className={classes('list-group', props.className)} role="presentation">
    <Button
      {...props.action}
      id={props.id}
      className="list-group-item list-group-item-action d-flex gap-3 align-items-center"
      autoFocus={props.autoFocus}
      icon={
        <Thumbnail square={true} size="sm" color={props.color}>
          <span className={`fa fa-${props.icon}`} />
        </Thumbnail>
      }
      label={
        <>
          <div className="flex-fill" role="presentation">
            <b className="mb-2">
              {props.label}
              {props.advanced &&
                <span className="badge bg-primary-subtle text-primary-emphasis ms-2">{trans('advanced')}</span>
              }
            </b>
            <p className="mb-0 text-body-secondary fs-sm" dangerouslySetInnerHTML={{ __html: props.description }} />
          </div>

          <span className="fa fa-chevron-right text-body-tertiary" aria-hidden={true} role="presentation" />
        </>
      }
    />
  </div>

WorkspaceCreationType.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  icon: T.string.isRequired,
  color: T.string.isRequired,
  label: T.string.isRequired,
  description: T.string.isRequired,
  advanced: T.bool,
  autoFocus: T.bool,
  action: T.shape({
    type: T.string.isRequired
  })
}

const WorkspaceCreation = (props) =>
  <>
    <WorkspaceCreationType
      id="create-from-model"
      className="mb-2"
      icon="stamp"
      color="var(--bs-pink)"
      autoFocus={true}
      label={trans('Créer à partir d\'un modèle')}
      description={trans('Choisissez un modèle préconfiguré pour commencer à ajouter vos contenus plus rapidement.')}
      action={{
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
      }}
    />

    <WorkspaceCreationType
      id="create-empty"
      className="mb-5"
      icon="book"
      color="var(--bs-cyan)"
      label={trans('Créer un espace vide')}
      description={trans('Créez un espace vide pour pouvoir le configurer comme vous le souhaitez.')}
      action={{
        type: CALLBACK_BUTTON,
        callback: () => props.changeStep('info')
      }}
      advanced={true}
    />

    <div className="fs-sm text-body-secondary text-uppercase fw-semibold mb-1">A partir d'un contenu existant</div>

    <WorkspaceCreationType
      id="create-from-copy"
      className="mb-2"
      icon="clone"
      color="var(--bs-purple)"
      label={trans('Copier un espace existant')}
      description={trans('Dupliquez un espace de la plateforme ainsi que tous ses contenus.')}
      action={{
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
      }}
    />

    <WorkspaceCreationType
      id="create-from-organization"
      className="mb-2"
      icon="building"
      color="var(--bs-teal)"
      label={trans('Ajouter depuis une autre organization')}
      description={trans('Sélectionnez un espace existant dans une autre organization pour le rendre accessible aux membres de l\'organization <b>Organization name</b>.')}
      action={{
        type: CALLBACK_BUTTON,
        callback: () => true
      }}
    />

    <WorkspaceCreationType
      id="create-from-import"
      className="mb-3"
      icon="file-zipper"
      color="var(--bs-orange)"
      label={trans('Importer une archive')}
      description={trans('Déposez une archive (.zip) générée à partir d\'une autre plateforme compatible.')}
      advanced={true}
      action={{
        type: MODAL_BUTTON,
        //callback: () => props.changeStep('upload'),
        modal: [MODAL_WORKSPACE_IMPORT]
      }}
    />
  </>

WorkspaceCreation.propTypes = {
  startCreation: T.func.isRequired,
  changeStep: T.func.isRequired
}

export {
  WorkspaceCreation
}
