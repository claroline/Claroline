import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentMenu} from '#/main/app/content/components/menu'

import {Sequence as SequenceTypes} from '#/main/evaluation/sequence/prop-types'

const CreationType = (props) =>
  <Modal
    {...omit(props, 'changeStep', 'startCreation')}
    title={trans('new_sequence', {}, 'evaluation')}
    subtitle={trans('new_sequence_desc', {}, 'evaluation')}
    centered={true}
  >
    <div className="modal-body">
      <ContentMenu
        className="mb-3"
        items={[
          {
            id: 'create-empty',
            icon: 'plus',
            label: trans('Créer une nouvelle séquence'),
            description: trans('Créez une séquence vide pour pouvoir la configurer comme vous le souhaitez.'),
            action: {
              type: CALLBACK_BUTTON,
              callback: () => {
                props.startCreation(SequenceTypes.defaultProps)
                props.changeStep('form')
              }
            }
          }, {
            id: 'create-from-copy',
            icon: 'clone',
            label: trans('Copier une séquence'),
            description: trans('Dupliquez une séquence de la plateforme pour la modifier avant de l\'utiliser dans votre espace.'),
            action: {
              type: CALLBACK_BUTTON,
              callback: () => props.changeStep('copy')
            },
            group: trans('from_existing_content')
          }, {
            id: 'create-from-import',
            icon: 'file-zipper',
            label: trans('import_archive', {}, 'actions'),
            description: trans('import_archive_desc', {}, 'actions'),
            displayed: false,
            action: {
              type: CALLBACK_BUTTON,
              callback: () => props.changeStep('upload')
            },
            advanced: true,
            group: trans('from_existing_content')
          }
        ]}
      />
    </div>
  </Modal>

CreationType.propTypes = {
  changeStep: T.func.isRequired,
  startCreation: T.func.isRequired
}

export {
  CreationType
}
