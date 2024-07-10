import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form'

import {selectors} from '#/main/evaluation//skills-framework/modals/creation/store'

const CreationUpload = (props) =>
  <Modal
    {...omit(props, 'changeStep')}
    title={trans('new_skills_framework', {}, 'evaluation')}
    /*subtitle={trans('L\'espace d\'activités est au coeur de votre formation.')}*/
    centered={true}
    onExited={props.reset}
  >
    <FormData
      name={selectors.STORE_NAME}
      flush={true}
      definition={[
        {
          title: trans('general'),
          fields: [
            {
              name: 'file',
              type: 'file',
              label: trans('file'),
              required: true,
              options: {
                autoUpload: false
              }
            }
          ]
        }
      ]}
    >
      <div className="modal-footer">
        <Button
          type={CALLBACK_BUTTON}
          label={trans('back')}
          className="btn btn-text-body me-auto"
          callback={() => props.changeStep('type')}
        />

        <Button
          type={CALLBACK_BUTTON}
          label={trans('Importer & Configurer', {}, 'actions')}
          className="btn btn-link"
          callback={() => true}
        />
        <Button
          type={CALLBACK_BUTTON}
          label={trans('import', {}, 'actions')}
          className="btn btn-primary"
          callback={() => true}
        />
      </div>
    </FormData>
  </Modal>

CreationUpload.propTypes = {
  changeStep: T.func.isRequired
}

export {
  CreationUpload
}
