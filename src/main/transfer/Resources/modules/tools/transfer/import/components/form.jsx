import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {Logs} from '#/main/transfer/tools/transfer/log/components/logs'

import {selectors} from '#/main/transfer/tools/transfer/import/store'
import {ImportExplanation} from '#/main/transfer/tools/transfer/import/components/explanation'
import {ImportSamples} from '#/main/transfer/tools/transfer/import/components/samples'

class ImportForm extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentSection: 'format'
    }
  }

  render() {
    const props = this.props

    const entity = props.match.params.entity
    const action = props.match.params.action

    const defaultFields = [
      {
        name: 'action',
        type: 'choice',
        label: trans('action'),
        onChange: (value) => {
          let action = ''
          if (value) {
            action = value.substring(value.indexOf('_') + 1)
          }

          props.history.push(`${this.props.path}/import/new/${entity}/${action}`)
          props.resetLog()
          // extra data is specific to the selected action, reset it to avoid saving wrong data
          props.updateProp('extra', null)
        },
        required: true,
        options: {
          noEmpty: false,
          condensed: true,
          choices: Object.keys(get(props.explanation, entity, [])).reduce((o, key) => Object.assign(o, {
            [entity + '_' + key]: trans(key, {}, 'transfer')
          }), {})
        }
      }, {
        name: 'file',
        type: 'file',
        label: trans('file'),
        required: true
      }, {
        name: 'format',
        type: 'choice',
        label: trans('format'),
        required: true,
        options: {
          noEmpty: true,
          choices: {
            csv: trans('csv')
          }
        },
        linked: [
          {
            name: 'header',
            type: 'boolean',
            label: trans('csv_header', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => true
          }, {
            name: 'rowDelimiter',
            type: 'string',
            label: trans('row_delimiter', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => '\\n'
          }, {
            name: 'columnDelimiter',
            type: 'string',
            label: trans('col_delimiter', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => ';'
          }, {
            name: 'arrayDelimiter',
            type: 'string',
            label: trans('list_delimiter', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => ','
          }
        ]
      }
    ]

    const explanationAction = get(props.explanation, entity+'.'+action)
    const additionalFields = explanationAction ? explanationAction.fields || []: []

    return (
      <FormData
        level={2}
        className="component-container"
        name={selectors.STORE_NAME + '.form'}
        title={trans(entity, {}, 'transfer')}
        buttons={true}
        save={{
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-upload',
          label: trans('import', {}, 'actions'),
          callback: () => {
            this.props.save().then(response => {
              const refresher = setInterval(() => {
                this.props.loadLog(response.id)
                if (this.props.logs && this.props.logs.total !== undefined && this.props.logs.processed === this.props.logs.total) {
                  clearInterval(refresher)
                }
              }, 2000)
            })

            this.setState({currentSection: 'log'})
          }
        }}
        cancel={{
          type: LINK_BUTTON,
          target: `${this.props.path}/import/new`,
          exact: true
        }}
        sections={[
          {
            title: trans('general'),
            primary: true,
            // group custom data for an action inside an extra object
            fields: defaultFields.concat(additionalFields.map(field => merge({}, field, {
              name: 'extra.'+field.name,
              linked: field.linked ? field.linked.map(linked => merge({}, linked, {name: 'extra.'+linked.name})) : []
            })))
          }
        ]}
      >
        {action &&
          <Fragment>
            <ul className="nav nav-tabs">
              <li>
                <Button
                  type={CALLBACK_BUTTON}
                  label={trans('format')}
                  callback={() => this.setState({currentSection: 'format'})}
                  active={'format' === this.state.currentSection}
                />
              </li>

              <li>
                <Button
                  type={CALLBACK_BUTTON}
                  label={trans('examples', {}, 'transfer')}
                  callback={() => this.setState({currentSection: 'samples'})}
                  active={'samples' === this.state.currentSection}
                />
              </li>

              <li>
                <Button
                  type={CALLBACK_BUTTON}
                  label={trans('log')}
                  callback={() => this.setState({currentSection: 'log'})}
                  active={'log' === this.state.currentSection}
                />
              </li>
            </ul>

            {'format' === this.state.currentSection &&
              <ImportExplanation schema={get(this.props.explanation, entity+'.'+action, {})} />
            }

            {'samples' === this.state.currentSection &&
              <ImportSamples
                format="csv"
                entity={entity}
                action={action}
                samples={get(this.props.samples, entity+'.'+action, [])}
              />
            }

            {'log' === this.state.currentSection &&
              <Logs />
            }
          </Fragment>
        }
      </FormData>
    )
  }
}

ImportForm.propTypes = {
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  match: T.shape({
    params: T.object.isRequired
  }).isRequired,
  explanation: T.object.isRequired,
  samples: T.object.isRequired,
  logs: T.object,

  save: T.func.isRequired,
  updateProp: T.func.isRequired,
  resetLog: T.func.isRequired,
  loadLog: T.func.isRequired
}

export {
  ImportForm
}