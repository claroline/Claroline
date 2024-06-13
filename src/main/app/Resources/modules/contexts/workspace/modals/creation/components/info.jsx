import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form'

import {selectors} from '#/main/app/contexts/workspace/modals/creation/store'

const CreationInfo = (props) =>
  <FormData
    name={selectors.STORE_NAME}
    flush={true}
    definition={[
      {
        title: trans('general'),
        fields: [
          {
            name: 'poster',
            type: 'poster',
            label: trans('poster'),
            hideLabel: true
          }, {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true,
            autoFocus: true
          }, {
            name: 'meta.description',
            type: 'string',
            label: trans('description_short'),
            help: trans('Décrivez succintement votre espace d\'activités (La description courte est affichée dans les listes et sur la vue "À propos").'),
            options: {
              long: true,
              minRows: 2
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
        label={trans('Créer & Configurer', {}, 'actions')}
        className="btn btn-link"
        callback={() => true}
      />
      <Button
        type={CALLBACK_BUTTON}
        label={trans('create', {}, 'actions')}
        className="btn btn-primary"
        callback={() => true}
      />
    </div>
  </FormData>

CreationInfo.propTypes = {
  changeStep: T.func.isRequired
}

export {
  CreationInfo
}
