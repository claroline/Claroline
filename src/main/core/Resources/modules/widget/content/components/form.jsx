import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

import {getWidget} from '#/main/core/widget/types'
import {WidgetContentIcon, WidgetSourceIcon} from '#/main/core/widget/content/components/icon'
import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'

const WidgetContentFormComponent = (props) => {
  let widget
  if (props.instance && props.instance.type) {
    widget = getWidget(props.instance.type)
  }

  return (
    <FormData
      level={props.level}
      name={props.name}
      sections={[
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
              calculated: (widgetInstance) => ({
                icon: <WidgetContentIcon type={widgetInstance.type} />,
                name: trans(widgetInstance.type, {}, 'widget'),
                description: trans(`${widgetInstance.type}_desc`, {}, 'widget')
              }),
              linked: [
                {
                  name: 'source',
                  type: 'type',
                  label: trans('data_source'),
                  displayed: !!props.instance.source,
                  hideLabel: true,
                  calculated: (widgetInstance) => ({
                    icon: <WidgetSourceIcon type={widgetInstance.source} />,
                    name: trans(widgetInstance.source, {}, 'data_sources'),
                    description: trans(`${widgetInstance.source}_desc`, {}, 'data_sources')
                  })
                }
              ]
            }
          ]
        }
      ]}
    >
      {widget &&
        <Await
          for={widget}
          then={module => {
            if (module.Parameters) {
              const parametersApp = module.Parameters()

              return React.createElement(parametersApp.component, {
                name: props.name,
                currentContext: props.currentContext,
                instance: props.instance
              })
            }
          }}
        />
      }
    </FormData>
  )
}

WidgetContentFormComponent.propTypes = {
  level: T.number,
  name: T.string.isRequired,
  currentContext: T.object.isRequired,
  instance: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired
}

const WidgetContentForm = connect(
  (state, ownProps) => ({
    instance: formSelectors.data(formSelectors.form(state, ownProps.name))
  })
)(WidgetContentFormComponent)

export {
  WidgetContentForm
}
