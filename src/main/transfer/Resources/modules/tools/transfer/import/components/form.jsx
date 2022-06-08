import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {trans, now} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/transfer/tools/transfer/import/store'
import {ImportExplanation} from '#/main/transfer/tools/transfer/import/components/explanation'
import {ImportSamples} from '#/main/transfer/tools/transfer/import/components/samples'

const isScheduled = (data) => get(data, 'scheduler._enable') || get(data, 'scheduler.scheduledDate')

class ImportForm extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentSection: 'format'
    }
  }

  render() {
    const props = this.props

    let entity = props.match.params.entity
    let action = props.match.params.action
    if (props.formData.action) {
      entity = props.formData.action.substring(0, props.formData.action.indexOf('_'))
      action = props.formData.action.substring(props.formData.action.indexOf('_') + 1)
    }

    const defaultFields = [
      {
        name: 'action',
        type: 'choice',
        label: trans('action'),
        disabled: !props.isNew,
        onChange: (value) => {
          let action = ''
          if (value) {
            action = value.substring(value.indexOf('_') + 1)
          }

          // extra data is specific to the selected action, reset it to avoid saving wrong data
          props.updateProp('extra', null)

          props.history.push(`${this.props.path}/import/new/${entity}/${action}`)
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
        name: 'name',
        type: 'string',
        label: trans('name')
      }, {
        name: 'file',
        type: 'file',
        label: trans('file'),
        required: true,
        disabled: !props.isNew
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
          callback: () => this.props.save(this.props.formData, this.props.isNew).then(importFile =>
            props.history.push(`${this.props.path}/import/history/${importFile.id}`)
          )
        }}
        cancel={{
          type: LINK_BUTTON,
          target: props.isNew ? `${this.props.path}/import/new` : `${this.props.path}/import/history/`+this.props.formData.id,
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
          }, {
            title: trans('format'),
            icon: 'fa fa-fw fa-file',
            fields: [
              {
                name: 'format',
                type: 'choice',
                label: trans('format'),
                required: true,
                hideLabel: true,
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
          }, {
            title: trans('planing', {}, 'scheduler'),
            icon: 'fa fa-fw fa-clock',
            displayed: props.schedulerEnabled,
            fields: [
              {
                name: 'scheduler._enable',
                type: 'boolean',
                label: trans('schedule', {}, 'scheduler'),
                calculated: isScheduled,
                onChange: (enabled) => {
                  if (enabled) {
                    props.updateProp('scheduler.executionType', 'once')
                    props.updateProp('scheduler.scheduledDate', now())
                  } else {
                    props.updateProp('scheduler', {})
                  }
                },
                linked: [
                  {
                    name: 'scheduler.executionType',
                    type: 'choice',
                    label: trans('type'),
                    displayed: isScheduled,
                    hideLabel: true,
                    required: true,
                    options: {
                      choices: {
                        once: trans('once', {}, 'scheduler'),
                        recurring: trans('recurring', {}, 'scheduler')
                      }
                    }
                  }, {
                    name: 'scheduler.scheduledDate',
                    type: 'date',
                    label: trans('scheduled_date', {}, 'scheduler'),
                    displayed: isScheduled,
                    required: true
                  }, {
                    name: 'scheduler.executionInterval',
                    type: 'number',
                    label: trans('interval', {}, 'scheduler'),
                    displayed: (data) => isScheduled(data) && 'recurring' === get(data, 'scheduler.executionType'),
                    required: true,
                    options: {
                      unit: trans('days')
                    }
                  }, {
                    name: 'scheduler.endDate',
                    type: 'date',
                    label: trans('end_date'),
                    displayed: (data) => isScheduled(data) && 'recurring' === get(data, 'scheduler.executionType'),
                    required: true
                  }
                ]
              }
            ]
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
  schedulerEnabled: T.bool,
  explanation: T.object.isRequired,
  samples: T.object.isRequired,
  formData: T.object,
  isNew: T.bool.isRequired,

  save: T.func.isRequired,
  updateProp: T.func.isRequired
}

export {
  ImportForm
}