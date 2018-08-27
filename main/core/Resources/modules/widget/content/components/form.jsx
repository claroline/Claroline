import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {Await} from '#/main/app/components/await'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

import {getWidget} from '#/main/core/widget/types'
import {WidgetContentType, WidgetSourceType} from '#/main/core/widget/content/components/type'
import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'

class WidgetContentFormComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      customForm: null
    }
  }

  render() {
    const widget = getWidget(this.props.instance.type)

    return (
      <FormData
        level={this.props.level}
        name={this.props.name}
        sections={[
          {
            id: 'general',
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'type',
                type: 'string',
                label: trans('widget'),
                hideLabel: true,
                render: (widgetInstance) => {
                  const ContentType =
                    <WidgetContentType
                      {...widgetInstance}
                    />

                  return ContentType
                },
                linked: [
                  {
                    name: 'source',
                    type: 'string',
                    label: trans('data_source'),
                    displayed: !!this.props.instance.source,
                    hideLabel: true,
                    render: (widgetInstance) => {
                      const SourceType =
                        <WidgetSourceType
                          {...widgetInstance}
                        />

                      return SourceType
                    }
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
                this.setState({customForm: module.Parameters()})
              }
            }}
          >
            {this.state.customForm && React.createElement(this.state.customForm.component, {
              name: this.props.name,
              instance: this.props.instance
            })}
          </Await>
        }
      </FormData>
    )
  }
}

WidgetContentFormComponent.propTypes = {
  level: T.number,
  name: T.string.isRequired,
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
