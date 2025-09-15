import React, {useCallback} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch} from 'react-redux'
import {useHistory} from 'react-router-dom'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {actions as formActions, Form, FormContent} from '#/main/app/content/form'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {route} from '#/main/evaluation/sequence/routing'
import {selectors} from '#/main/evaluation/sequence/modals/creation/store'

const CreationForm = (props) => {
  const history = useHistory()

  const dispatch = useDispatch()
  const reset = useCallback(() => dispatch(formActions.reset(selectors.STORE_NAME, {}, true)), [selectors.STORE_NAME])

  return (
    <Modal
      {...omit(props, 'create', 'changeStep')}
      title={trans('new_sequence', {}, 'evaluation')}
      centered={true}
      onExited={reset}
    >
      <Form
        name={selectors.STORE_NAME}
        flush={true}
      >
        <FormContent
          className="modal-body"
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
                  help: trans('Décrivez succinctement votre séquence (La description courte est affichée dans les listes.'),
                  recommended: true,
                  options: {
                    long: true,
                    minRows: 2
                  }
                }, {
                  name: 'meta.published',
                  label: trans('publish_sequence', {}, 'evaluation'),
                  type: 'boolean',
                  help: trans('publish_sequence_help', {}, 'evaluation')
                }
              ]
            }
          ]}
        />

        <div className="modal-footer mt-n5">
          <Button
            type={CALLBACK_BUTTON}
            label={trans('back')}
            className="btn btn-text-body me-auto"
            callback={() => props.changeStep('type')}
          />

          <Button
            type={CALLBACK_BUTTON}
            label={trans('create_and_configure', {}, 'actions')}
            className="btn btn-link"
            callback={() => props.create().then((sequence) => {
              history.push(route(sequence)+'/edit')
            })}
          />
          <Button
            type={CALLBACK_BUTTON}
            label={trans('create', {}, 'actions')}
            className="btn btn-primary"
            htmlType="submit"
            callback={props.create}
          />
        </div>
      </Form>
    </Modal>
  )
}

CreationForm.propTypes = {
  create: T.func.isRequired,
  changeStep: T.func.isRequired
}

export {
  CreationForm
}
