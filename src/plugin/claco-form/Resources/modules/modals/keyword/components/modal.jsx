import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {Keyword as KeywordTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {selectors} from '#/plugin/claco-form/modals/keyword/store'

const KeywordModal = (props) =>
  <Modal
    {...omit(props, 'saveEnabled', 'isNew', 'formData', 'keyword', 'loadKeyword', 'saveKeyword', 'clacoFormId')}
    icon="fa fa-fw fa-font"
    title={trans('keyword', {}, 'clacoform')}
    subtitle={(props.keyword && props.keyword.name) || trans('new_keyword', {}, 'clacoform')}
    onEntering={() => props.loadKeyword(props.clacoFormId, props.keyword)}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          id: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true,
              options: {
                unique: {
                  check: [
                    'claro_claco_form_get_keyword_by_name_excluding_uuid',
                    {clacoForm: props.clacoFormId, uuid: props.keyword ? props.keyword.id : null}
                  ]
                }
              }
            }
          ]
        }
      ]}
    />

    <Button
      className="modal-btn btn btn-primary"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => props.saveKeyword(props.formData, props.isNew, (keyword) => {
        if (props.onSave) {
          props.onSave(keyword)
        }

        props.fadeModal()
      })}
    />
  </Modal>

KeywordModal.propTypes = {
  clacoFormId:T.string.isRequired,
  isNew: T.bool.isRequired,
  saveEnabled: T.bool.isRequired,
  keyword: T.shape(KeywordTypes.propTypes),
  formData: T.object,
  loadKeyword: T.func.isRequired,
  saveKeyword: T.func.isRequired,
  onSave: T.func,
  fadeModal: T.func.isRequired
}

export {
  KeywordModal
}