import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useHistory} from 'react-router-dom'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {actions as formActions, FormData} from '#/main/app/content/form'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/evaluation//skills-framework/modals/creation/store'
import {useDispatch} from 'react-redux'

const CreationForm = (props) => {
  const history = useHistory()

  const dispatch = useDispatch()
  const create = () => dispatch(formActions.save(selectors.STORE_NAME, ['apiv2_skills_framework_create']))
  const reset = () => dispatch(formActions.reset(selectors.STORE_NAME, {}, true))

  return (
    <Modal
      {...omit(props, 'changeStep')}
      title={trans('new_skills_framework', {}, 'evaluation')}
      centered={true}
      onExited={reset}
    >
      <FormData
        name={selectors.STORE_NAME}
        flush={true}
        definition={[
          {
            title: trans('general'),
            fields: [
              {
                name: 'name',
                type: 'string',
                label: trans('name'),
                required: true,
                autoFocus: true
              }, {
                name: 'description',
                type: 'string',
                label: trans('description_short'),
                help: trans('Décrivez succintement votre référentiel de compétences (La description courte est affichée dans les listes et sur la vue "À propos").'),
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
              create().then((workspace) => {
                props.fadeModal()

                //history.push(route(workspace)+'/edit')
              })
            }}
          />
          <Button
            type={CALLBACK_BUTTON}
            label={trans('create', {}, 'actions')}
            className="btn btn-primary"
            htmlType="submit"
            callback={() => {
              create().then((workspace) => {
                props.fadeModal()

                //history.push(route(workspace))
              })
            }}
          />
        </div>
      </FormData>
    </Modal>
  )
}

CreationForm.propTypes = {
  changeStep: T.func.isRequired
}

export {
  CreationForm
}
