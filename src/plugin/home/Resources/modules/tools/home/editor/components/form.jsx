import React, {useCallback} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {actions as formActions, Form, FormContent, selectors as formSelectors} from '#/main/app/content/form'

const restrictedByDates = (tab) => get(tab, 'restrictions.enableDates') || !isEmpty(get(tab, 'restrictions.dates'))
const restrictedByCode = (tab) => get(tab, 'restrictions.enableCode') || !!get(tab, 'restrictions.code')
const restrictedByRoles = (tab) => get(tab, 'restrictions.enableRoles') || !isEmpty(get(tab, 'restrictions.roles'))

const TabForm = (props) => {
  const dispatch = useDispatch()

  const formData = useSelector((state) => formSelectors.data(formSelectors.form(state, props.name)))
  const saveEnabled = useSelector((state) => formSelectors.saveEnabled(formSelectors.form(state, props.name)))

  const update = useCallback((field, value) => {
    dispatch(formActions.updateProp(props.name, field, value))
  }, [props.name])
  const setErrors = useCallback((errors) => {
    dispatch(formActions.setErrors(props.name, errors))
  }, [props.name])

  return (
    <Form
      name={props.name}
      flush={true}
      level={2}
      displayLevel={5}
    >
      <FormContent
        className="modal-body"
        name={props.name}
        level={2}
        displayLevel={5}
        flush={true}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'poster',
                type: 'poster',
                hideLabel: true,
                label: trans('poster')
              }, {
                name: 'longTitle',
                type: 'string',
                label: trans('title'),
                required: true,
                onChange: (title) => update('title', title.substring(0, 20))
              }
            ]
          }, {
            icon: 'fa fa-fw fa-desktop',
            title: trans('display_parameters'),
            fields: [
              {
                name: 'restrictions.hidden',
                type: 'boolean',
                label: trans('restrict_hidden')
              }, {
                name: 'title',
                type: 'string',
                label: trans('title'),
                help: trans('menu_title_help'),
                options: {
                  maxLength: 64
                },
                onChange: (value) => {
                  if (isEmpty(value) && 0 === formData.icon.length) {
                    setErrors({title: 'Ce champ ne peux pas être vide si l\'onglet n\'a pas d\'icône'})
                  }
                }
              }, {
                name: 'icon',
                type: 'icon',
                label: trans('icon'),
                help: trans('icon_tab_help'),
                onChange: (icon) => {
                  if (0 === icon.length && 0 === formData.title.length) {
                    setErrors({icon: 'Ce champ ne peux pas être vide si l\'onglet n\'a pas de titre.'})
                  }
                }
              }
            ]
          }, {
            icon: 'fa fa-fw fa-key',
            title: trans('access_restrictions'),
            fields: [
              {
                name: 'restrictions.enableDates',
                label: trans('restrict_by_dates'),
                type: 'boolean',
                calculated: restrictedByDates,
                onChange: activated => {
                  if (!activated) {
                    update('restrictions.dates', [])
                  }
                },
                linked: [
                  {
                    name: 'restrictions.dates',
                    type: 'date-range',
                    label: trans('access_dates'),
                    displayed: restrictedByDates,
                    required: true,
                    options: {
                      time: true
                    }
                  }
                ]
              }, {
                name: 'restrictions.enableCode',
                label: trans('restrict_by_code'),
                help: trans('restrict_by_code_help'),
                type: 'boolean',
                calculated: restrictedByCode,
                onChange: activated => {
                  if (!activated) {
                    update('restrictions.code', '')
                  }
                },
                linked: [
                  {
                    name: 'restrictions.code',
                    label: trans('access_code'),
                    displayed: restrictedByCode,
                    type: 'password',
                    required: true
                  }
                ]
              }, {
                name: 'restrictions.enableRoles',
                type: 'boolean',
                label: trans('restrictions_by_roles', {}, 'widget'),
                calculated: restrictedByRoles,
                onChange: (checked) => {
                  if (!checked) {
                    update('restrictions.roles', [])
                  }
                },
                linked: [
                  {
                    name: 'restrictions.roles',
                    label: trans('roles'),
                    displayed: restrictedByRoles,
                    type: 'role',
                    required: true,
                    options: {
                      multiple: true,
                      picker: {
                        personal: false,
                        contextType: props.currentContext.type,
                        contextId: get(props.currentContext, 'data.id')
                      }
                    }
                  }
                ]
              }
            ]
          }
        ]}
      />

      <div className="modal-footer mt-n5">
        {props.children}

        <Button
          type={CALLBACK_BUTTON}
          label={trans(props.isNew ? 'add_home_page' : 'save_home_page', {}, 'actions')}
          className="btn btn-primary"
          htmlType="submit"
          disabled={!saveEnabled}
          callback={() => props.onSave(formData)}
        />
      </div>
    </Form>
  )
}

TabForm.propTypes = {
  name: T.string.isRequired,

  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }),
  isNew: T.bool.isRequired,
  onSave: T.func.isRequired,
  children: T.node
}

export {
  TabForm
}
