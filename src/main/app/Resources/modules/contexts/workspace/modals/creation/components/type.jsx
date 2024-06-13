import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {CallbackButton} from '#/main/app/buttons'
import {Thumbnail} from '#/main/app/components/thumbnail'
import {trans} from '#/main/app/intl'

const CreationTypeItem = (props) =>
  <div className={classes('list-group', props.className)} role="presentation">
    <CallbackButton
      className="list-group-item list-group-item-action d-flex gap-3 align-items-center"
      callback={props.onSelect}
      autoFocus={props.autoFocus}
    >
      <Thumbnail square={true} size="sm" color={props.color}>
        <span className={`fa fa-${props.icon}`} />
      </Thumbnail>

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
    </CallbackButton>
  </div>

CreationTypeItem.propTypes = {
  className: T.string,
  icon: T.string.isRequired,
  color: T.string.isRequired,
  label: T.string.isRequired,
  description: T.string.isRequired,
  onSelect: T.func.isRequired,
  advanced: T.bool,
  autoFocus: T.bool
}

const CreationType = (props) =>
  <div className="modal-body">
    <CreationTypeItem
      className="mb-2"
      icon="stamp"
      color="var(--bs-pink)"
      autoFocus={true}
      label={trans('Créer à partir d\'un modèle')}
      description={trans('Choisissez un modèle préconfiguré pour commencer à ajouter vos contenus plus rapidement.')}
      onSelect={() => props.changeStep('models')}
    />

    <CreationTypeItem
      className="mb-5"
      icon="book"
      color="var(--bs-cyan)"
      label={trans('Créer un espace vide')}
      description={trans('Créer un espace vide pour pouvoir le configurer comme vous le souhaitez.')}
      onSelect={() => props.changeStep('info')}
      advanced={true}
    />

    <div className="fs-sm text-body-secondary text-uppercase fw-semibold mb-1">A partir d'un contenu existant</div>

    <CreationTypeItem
      className="mb-2"
      icon="clone"
      color="var(--bs-purple)"
      label={trans('Copier un espace existant')}
      description={trans('Dupliquez un espace de la plateforme ainsi que tous ses contenus.')}
      onSelect={() => true}
    />

    <CreationTypeItem
      className="mb-2"
      icon="building"
      color="var(--bs-teal)"
      label={trans('Ajouter depuis une autre organization')}
      description={trans('Sélectionnez un espace existant dans une autre organization pour le rendre accessible aux membres de l\'organization <b>Organization name</b>.')}
      onSelect={() => true}
    />

    <CreationTypeItem
      className="mb-3"
      icon="file-zipper"
      color="var(--bs-orange)"
      label={trans('Importer une archive')}
      description={trans('Déposez une archive (.zip) générée à partir d\'une autre plateforme compatible.')}
      onSelect={() => props.changeStep('upload')}
      advanced={true}
    />
  </div>

CreationType.propTypes = {
  changeStep: T.func.isRequired
}

export {
  CreationType
}
