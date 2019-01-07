import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/claco-form/modals/category/store/selectors'
import {Category as CategoryTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'

const CategoryFormModal = props =>
  <Modal
    {...omit(props, 'saveEnabled', 'formData', 'fields', 'category', 'loadCategory', 'saveCategory')}
    icon="fa fa-fw fa-object-group"
    title={trans('category')}
    subtitle={(props.category && props.category.name) || trans('new_category')}
    onEntering={() => props.loadCategory(props.category)}
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
              required: true
            }, {
              name: 'managers',
              label: trans('managers'),
              type: 'users'
            }, {
              name: 'fieldValues',
              label: trans('fields'),
              type: 'choice',
              options: {
                condensed: false,
                inline: false,
                multiple: true,
                choices: props.fields.reduce((choices, field) => Object.assign(choices, {
                  [field.id]: field.name
                }), {})
              }
            }
          ]
        }, {
          id: 'display',
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'details.color',
              type: 'color',
              label: trans('color')
            }
          ]
        }, {
          id: 'notifications',
          icon: 'fa fa-fw fa-bell',
          title: trans('notifications'),
          fields: [
            {
              name: 'notifications',
              label: trans('notified_actions'),
              type: 'choice',
              options: {
                multiple: true,
                inline: false,
                choices: {
                  notify_addition: trans('addition', {}, 'clacoform'),
                  notify_edition: trans('edition', {}, 'clacoform'),
                  notify_removal: trans('removal', {}, 'clacoform'),
                  notify_pending_comment: trans('comment_to_moderate', {}, 'clacoform')
                }
              },
              onChange: (value) => {
                props.updateProp('notify_addition', -1 !== value.indexOf('notify_addition'))
                props.updateProp('notify_edition', -1 !== value.indexOf('notify_edition'))
                props.updateProp('notify_removal', -1 !== value.indexOf('notify_removal'))
                props.updateProp('notify_pending_comment', -1 !== value.indexOf('notify_pending_comment'))
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
      callback={() => {
        props.saveCategory(props.formData)
        props.fadeModal()
      }}
    />
  </Modal>

CategoryFormModal.propTypes = {
  saveEnabled: T.bool.isRequired,
  formData: T.shape(
    CategoryTypes.propTypes
  ),
  category: T.shape(
    CategoryTypes.propTypes
  ),
  fields: T.arrayOf(T.shape({
    // TODO : field propTypes
  })),
  loadCategory: T.func.isRequired,
  saveCategory: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  CategoryFormModal
}
