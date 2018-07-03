import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {Await} from '#/main/app/components/await'
import {FormContainer} from '#/main/core/data/form/containers/form'
import {select as formSelectors} from '#/main/core/data/form/selectors'

import {getWidget} from '#/main/core/widget/types'
import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'

class WidgetContentFormComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      customForm: null
    }
  }

  render() {
    return (
      <div>
        <FormContainer
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
                  type: 'translation',
                  label: trans('widget'),
                  readOnly: true,
                  hideLabel: true,
                  options: {
                    domain: 'widget'
                  }
                }, {
                  name: 'source',
                  type: 'translation',
                  label: trans('data_source'),
                  readOnly: true,
                  hideLabel: true,
                  displayed: (content) => !!content.source,
                  options: {
                    domain: 'data'
                  }
                }
              ]
            }
          ]}
        />

        <Await
          for={getWidget(this.props.instance.type)()}
          then={module => {
            if (module.Parameters) {
              this.setState({customForm: module.Parameters()})
            }
          }}
        >
          {this.state.customForm && React.createElement(this.state.customForm.component)}
        </Await>
      </div>
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
