import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form'

import {selectors} from '#/main/core/workspace/modals/creation/store'
import {useHistory} from 'react-router-dom'
import {route} from '#/main/core/workspace/routing'

const CreationInfo = (props) => {
  const history = useHistory()

  return (
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
          callback={() => {
            props.create().then((workspace) => {
              props.fadeModal()

              history.push(route(workspace)+'/edit')
            })
          }}
        />
        <Button
          type={CALLBACK_BUTTON}
          label={trans('create', {}, 'actions')}
          className="btn btn-primary"
          htmlType="submit"
          callback={() => {
            props.create().then((workspace) => {
              props.fadeModal()

              history.push(route(workspace))
            })
          }}
        />
      </div>
    </FormData>
  )
}

CreationInfo.propTypes = {
  create: T.func.isRequired,
  fadeModal: T.func.isRequired,
  changeStep: T.func.isRequired
}

export {
  CreationInfo
}
