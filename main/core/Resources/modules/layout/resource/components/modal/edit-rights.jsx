import React from 'react'
import Modal from 'react-bootstrap/lib/Modal'

import {t}         from '#/main/core/translation'
import {t_res}     from '#/main/core/layout/resource/translation'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'

export const MODAL_RESOURCE_RIGHTS = 'MODAL_RESOURCE_RIGHTS'

const EditRightsModal = props =>
  <BaseModal
    bsSize="large"
    icon="fa fa-fw fa-lock"
    title={t_res('edit-rights')}
    className="resource-edit-rights-modal"
    {...props}
  >
    <ul className="nav nav-tabs">
      <li className="active">
        <a href="">Simple</a>
      </li>

      <li>
        <a href="">Avancé</a>
      </li>
    </ul>

    <Modal.Body>

    </Modal.Body>

    <button className="modal-btn btn btn-primary" onClick={() => true}>
      {t('save')}
    </button>
  </BaseModal>

EditRightsModal.propTypes = {

}

export {EditRightsModal}
