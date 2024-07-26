import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentMenu} from '#/main/app/content/components/menu'

const CreationType = (props) =>
  <Modal
    {...omit(props, 'changeStep')}
    title={trans('new_skills_framework', {}, 'evaluation')}
    centered={true}
    onExited={props.reset}
  >
    <div className="modal-body">
      <ContentMenu
        className="mb-3"
        items={[
          {
            id: 'create-empty',
            icon: 'plus',
            label: trans('Créer un nouveau référentiel'),
            description: trans('Créez un référentiel de compétences vide que vous pourrez configurer pour votre espace d\'activités.'),
            action: {
              type: CALLBACK_BUTTON,
              callback: () => props.changeStep('form')
            }
          }, {
            id: 'select',
            icon: 'hand-pointer',
            label: trans('Ajouter un référentiel existant'),
            description: trans('Sélectionnez le référentiel de compétences à utiliser dans votre espace.'),
            action: {
              type: CALLBACK_BUTTON,
              callback: () => true
            },
            group: 'A partir d\'un contenu existant'
          }, {
            id: 'create-from-copy',
            icon: 'clone',
            label: trans('Copier un référentiel existant'),
            description: trans('Dupliquez un référentiel de la plateforme pour le modifier avant de l\'utiliser dans votre espace.'),
            action: {
              type: CALLBACK_BUTTON,
              callback: () => props.changeStep('copy')
            },
            group: 'A partir d\'un contenu existant'
          }, {
            id: 'create-from-import',
            icon: 'file-code',
            label: trans('Importer un fichier'),
            description: trans('Déposez un fichier (.json) au format compatible.'),
            action: {
              type: CALLBACK_BUTTON,
              callback: () => props.changeStep('upload')
            },
            advanced: true,
            group: 'A partir d\'un contenu existant'
          }
        ]}
      />
    </div>
  </Modal>

CreationType.propTypes = {
  changeStep: T.func.isRequired,
  reset: T.func.isRequired
}

export {
  CreationType
}
