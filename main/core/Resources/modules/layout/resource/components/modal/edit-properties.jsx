import React, {PropTypes as T} from 'react'
import Modal from 'react-bootstrap/lib/Modal'

import {t} from '#/main/core/translation'
import {BaseModal} from '#/main/core/modal/components/base.jsx'
import {FormGroup} from '#/main/core/form/components/form-group.jsx'

export const MODAL_RESOURCE_PROPERTIES = 'MODAL_RESOURCE_PROPERTIES'

const EditPropertiesModal = props =>
  <BaseModal {...props} className="search-modal">
    <Modal.Body>
      <FormGroup
        controlId="resource-title"
        label={t('name')}
      >
        <input
          id="resource-title"
          type="text"
          className="form-control"
          value={null}
          onChange={() => true}
        />
      </FormGroup>

      <div className="checkbox">
        <label htmlFor="resource-published">
          <input
            id="resource-published"
            type="checkbox"
            checked={true}
            onChange={() => true}
          />
          {t('Resource is published')}
        </label>
      </div>

    </Modal.Body>

    <Modal.Footer>
      <button className="btn btn-default" onClick={props.fadeModal}>
        {t('cancel')}
      </button>
      <button className="btn btn-primary" onClick={() => true}>
        {t('save')}
      </button>
    </Modal.Footer>
  </BaseModal>

EditPropertiesModal.propTypes = {
  fadeModal: T.func.isRequired
}

export {EditPropertiesModal}
