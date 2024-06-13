import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form'

import {selectors} from '#/main/app/contexts/workspace/modals/creation/store'

const CreationUpload = (props) =>
  <FormData
    name={selectors.STORE_NAME}
    flush={true}
    definition={[
      {
        title: trans('general'),
        fields: [
          {
            name: 'archive',
            type: 'file',
            label: trans('archive'),
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

CreationUpload.propTypes = {
  changeStep: T.func.isRequired
}

export {
  CreationUpload
}
