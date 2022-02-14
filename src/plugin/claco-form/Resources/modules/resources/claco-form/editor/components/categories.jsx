import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans, transChoice} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {ClacoForm as ClacoFormTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {MODAL_CATEGORY_FORM} from '#/plugin/claco-form/modals/category'
import {Button} from '#/main/app/action'

const EditorCategories = props =>
  <Fragment>
    <FormData
      level={2}
      title={trans('categories', {}, 'clacoform')}
      name={selectors.STORE_NAME+'.clacoFormForm'}
      buttons={true}
      target={(clacoForm) => ['apiv2_clacoform_update', {id: clacoForm.id}]}
      cancel={{
        type: LINK_BUTTON,
        target: props.path,
        exact: true
      }}
      sections={[
        {
          id: 'general',
          title: trans('general'),
          fields: [
            {
              name: 'details.display_categories',
              type: 'boolean',
              label: trans('label_display_categories', {}, 'clacoform'),
              help: trans('display_categories_help', {}, 'clacoform')
            }
          ]
        }
      ]}
    />

    <ListData
      name={selectors.STORE_NAME+'.clacoFormForm.categories'}
      fetch={{
        url: ['apiv2_clacoformcategory_list', {clacoForm: props.clacoForm.id}],
        autoload: !!props.clacoForm.id
      }}
      definition={[
        {
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true
        }, {
          name: 'managers',
          type: 'string',
          label: trans('managers'),
          displayed: true,
          render: (rowData) => rowData.managers.map(m => m.firstName + ' ' + m.lastName).join(', ')
        }, {
          name: 'details.notify_addition',
          type: 'boolean',
          alias: 'notify_addition',
          label: trans('addition', {}, 'clacoform'),
          displayed: true,
          sortable: false
        }, {
          name: 'details.notify_edition',
          type: 'boolean',
          alias: 'notify_edition',
          label: trans('edition', {}, 'clacoform'),
          displayed: true,
          sortable: false
        }, {
          name: 'details.notify_removal',
          type: 'boolean',
          alias: 'notify_removal',
          label: trans('removal', {}, 'clacoform'),
          displayed: true,
          sortable: false
        }, {
          name: 'details.notify_pending_comment',
          type: 'boolean',
          alias: 'notify_pending_comment',
          label: trans('comment'),
          displayed: true,
          sortable: false
        }
      ]}
      actions={(rows) => [
        {
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_CATEGORY_FORM, {
            category: rows[0],
            fields: props.clacoForm.fields,
            saveCategory: (category) => props.saveCategory(props.clacoForm.id, category, false)
          }],
          scope: ['object']
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete', {}, 'actions'),
          dangerous: true,
          confirm: {
            title: trans('objects_delete_title'),
            message: transChoice('objects_delete_question', rows.length, {count: rows.length}, 'platform')
          },
          callback: () => props.deleteCategories(rows)
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-refresh',
          label: trans('recalculate', {}, 'actions'),
          callback: () => props.assignCategory(rows[0]),
          scope: ['object'],
          displayed: rows[0].fieldsValues && 0 !== rows[0].fieldsValues.length
        }
      ]}
    />

    <Button
      type={MODAL_BUTTON}
      className="btn btn-block btn-emphasis component-container"
      label={trans('create_a_category', {}, 'clacoform')}
      modal={[MODAL_CATEGORY_FORM, {
        fields: props.clacoForm.fields,
        saveCategory: (category) => props.saveCategory(props.clacoForm.id, category, true)
      }]}
      primary={true}
    />
  </Fragment>

EditorCategories.propTypes = {
  path: T.string.isRequired,
  clacoForm: T.shape(
    ClacoFormTypes.propTypes
  ),
  saveCategory: T.func.isRequired,
  assignCategory: T.func.isRequired,
  deleteCategories: T.func.isRequired
}

export {
  EditorCategories
}