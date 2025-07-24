import React, {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Form, FormContent, selectors as formSelectors} from '#/main/app/content/form'

import {getWidget} from '#/main/core/widget/types'
import {WidgetContentIcon, WidgetSourceIcon} from '#/main/core/widget/content/components/icon'

const WidgetContentForm = (props) => {
  const [widgetApp, setWidgetApp] = useState(null)

  const formData = useSelector((state) => formSelectors.data(formSelectors.form(state, props.name)))
  const saveEnabled = useSelector((state) => formSelectors.saveEnabled(formSelectors.form(state, props.name)))

  useEffect(() => {
    if (!isEmpty(formData)) {
      getWidget(formData.type).then(module => {
        if (get(module, 'default.editor')) {
          setWidgetApp({component: get(module, 'default.editor')})
        }
      })
    }
  }, [get(formData, 'type')])

  return (
    <Form
      name={props.name}
      flush={true}
      level={2}
      displayLevel={5}
    >
      <div className="modal-body">
        <FormContent
          className="mb-5"
          name={props.name}
          level={2}
          displayLevel={5}
          flush={true}
          definition={[
            {
              id: 'general',
              title: trans('general'),
              primary: true,
              fields: [
                {
                  name: 'type',
                  type: 'type',
                  label: trans('widget'),
                  hideLabel: true,
                  calculated: (widgetInstance) => {
                    if (formData.source) {
                      return {
                        icon: <WidgetSourceIcon type={widgetInstance.source}/>,
                        name: trans(widgetInstance.source, {}, 'data_sources'),
                        description: trans(`${widgetInstance.source}_desc`, {}, 'data_sources')
                      }
                    }

                    return {
                      icon: <WidgetContentIcon type={widgetInstance.type}/>,
                      name: trans(widgetInstance.type, {}, 'widget'),
                      description: trans(`${widgetInstance.type}_desc`, {}, 'widget')
                    }
                  }
                }
              ]
            }
          ]}
        />
        {!isEmpty(widgetApp) && createElement(widgetApp.component, {
          name: props.name,
          currentContext: props.currentContext,
          instance: formData
        })}
      </div>

      <div className="modal-footer mt-n5">
        {props.children}

        <Button
          type={CALLBACK_BUTTON}
          label={trans(props.isNew ? 'add_widget' : 'save_widget', {}, 'widget')}
          className="btn btn-primary"
          htmlType="submit"
          disabled={!saveEnabled}
          callback={() => props.onSave(formData)}
        />
      </div>
    </Form>
  )
}

WidgetContentForm.propTypes = {
  level: T.number,
  name: T.string.isRequired,
  currentContext: T.object.isRequired
}

export {
  WidgetContentForm
}
