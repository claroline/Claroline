import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'

const RegistrationModal = props =>
  <Modal
    {...omit(props, 'course')}
    icon="fa fa-fw fa-user-plus"
    title={trans('registration')}
    subtitle={props.course.name}
    poster={props.course.poster ? props.course.poster.url : undefined}
  >
    <div>Détails de la session par défaut</div>
    <div>Notification pas de session disponible (liste attente)</div>
    <div>Liste des sessions disponibles si pas de session par défaut</div>

    <Button
      className="btn modal-btn"
      type={CALLBACK_BUTTON}
      label={trans('Voir les autres sessions', {}, 'actions')}
      callback={() => {

      }}
    />

    <Button
      className="btn modal-btn"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('self-register', {}, 'actions')}
      callback={() => {

      }}
    />
  </Modal>

RegistrationModal.propTypes = {
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired
}

export {
  RegistrationModal
}
