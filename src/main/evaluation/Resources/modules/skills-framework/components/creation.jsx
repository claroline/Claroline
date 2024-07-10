import React from 'react'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentCreation} from '#/main/app/content/components/creation'
import {MODAL_SKILLS_FRAMEWORKS_CREATION} from '#/main/evaluation/skills-framework/modals/creation'

const SkillsFrameworkCreation = () =>
  <ContentCreation
    types={[
      {
        id: 'create-empty',
        icon: 'plus',
        label: trans('Créer un nouveau référentiel'),
        description: trans('Créez un référentiel de compétences vide que vous pourrez configurer pour votre espace d\'activités.'),
        action: {
          type: MODAL_BUTTON,
          modal: [MODAL_SKILLS_FRAMEWORKS_CREATION, {
            step: 'form'
          }]
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
          type: MODAL_BUTTON,
          modal: [MODAL_SKILLS_FRAMEWORKS_CREATION, {
            step: 'copy'
          }]
        },
        group: 'A partir d\'un contenu existant'
      }, {
        id: 'create-from-import',
        icon: 'file-code',
        label: trans('Importer un fichier'),
        description: trans('Déposez un fichier (.json) au format compatible.'),
        action: {
          type: MODAL_BUTTON,
          modal: [MODAL_SKILLS_FRAMEWORKS_CREATION, {
            step: 'upload'
          }]
        },
        advanced: true,
        group: 'A partir d\'un contenu existant'
      }
    ]}
  />

export {
  SkillsFrameworkCreation
}
